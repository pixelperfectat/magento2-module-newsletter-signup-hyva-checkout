<?php declare(strict_types=1);

namespace PixelPerfect\NewsletterSignupHyvaCheckout\Test\Unit\Magewire\Checkout;

use PHPUnit\Framework\TestCase;
use PixelPerfect\NewsletterSignupHyvaCheckout\Api\Service\CheckoutSignupLineInterface;
use PixelPerfect\NewsletterSignupHyvaCheckout\Magewire\Checkout\SignupLine;

class SignupLineTest extends TestCase
{
    /** Whether core invites the email address on the cart */
    private bool $invited = true;

    private bool $storedChoice = false;

    private SignupLine $component;

    protected function setUp(): void
    {
        $line = $this->createMock(CheckoutSignupLineInterface::class);
        $line->method('isShown')->willReturnCallback(fn (): bool => $this->invited);
        $line->method('isOptedIn')->willReturnCallback(fn (): bool => $this->storedChoice);
        $line->method('setOptIn')->willReturnCallback(function (bool $optIn): bool {
            $this->storedChoice = $optIn && $this->invited;
            return $this->storedChoice;
        });
        $line->method('getBenefitText')->willReturn('Get news first.');

        $this->component = new SignupLine($line);
    }

    public function testAnInvitedShopperSeesTheLineUnticked(): void
    {
        $this->component->mount();

        $this->assertTrue($this->component->shown);
        $this->assertFalse($this->component->optIn);
    }

    public function testAShopperWhoIsNotInvitedDoesNotSeeTheLine(): void
    {
        $this->invited = false;

        $this->component->mount();

        $this->assertFalse($this->component->shown);
    }

    public function testAChoiceStoredEarlierIsShownTicked(): void
    {
        $this->storedChoice = true;

        $this->component->mount();

        $this->assertTrue($this->component->optIn);
    }

    public function testTickingStoresTheChoice(): void
    {
        $this->component->mount();

        $this->assertTrue($this->component->updatedOptIn(true));

        $this->assertTrue($this->storedChoice);
    }

    public function testUntickingClearsTheChoice(): void
    {
        $this->storedChoice = true;
        $this->component->mount();

        $this->assertFalse($this->component->updatedOptIn(false));

        $this->assertFalse($this->storedChoice);
    }

    public function testARefusedTickLeavesTheBoxUnticked(): void
    {
        $this->component->mount();
        $this->invited = false;

        $this->assertFalse($this->component->updatedOptIn(true));
    }

    public function testANewEmailOnTheCartHidesTheLineAndDropsTheTick(): void
    {
        $this->component->mount();
        $this->component->updatedOptIn(true);
        $this->invited = false;

        $this->component->reevaluate();

        $this->assertFalse($this->component->shown);
        $this->assertFalse($this->component->optIn);
        $this->assertFalse($this->storedChoice);
    }

    public function testANewEmailOnTheCartThatIsInvitedShowsTheLine(): void
    {
        $this->invited = false;
        $this->component->mount();
        $this->invited = true;

        $this->component->reevaluate();

        $this->assertTrue($this->component->shown);
        $this->assertFalse($this->component->optIn);
    }

    public function testTheLineIsCheckedAgainWheneverAnAddressOfTheCartChanges(): void
    {
        $this->assertSame(
            [
                'shipping_address_saved' => 'reevaluate',
                'shipping_address_activated' => 'reevaluate',
                'billing_address_saved' => 'reevaluate',
                'billing_address_activated' => 'reevaluate',
                'billing_as_shipping_address_updated' => 'reevaluate',
            ],
            $this->component->getListeners()
        );
    }

    public function testTheBenefitTextIsTheConfiguredOne(): void
    {
        $this->assertSame('Get news first.', $this->component->getBenefitText());
    }
}
