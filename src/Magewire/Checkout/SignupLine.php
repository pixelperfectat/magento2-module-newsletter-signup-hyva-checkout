<?php declare(strict_types=1);

namespace PixelPerfect\NewsletterSignupHyvaCheckout\Magewire\Checkout;

use Magewirephp\Magewire\Component;
use PixelPerfect\NewsletterSignupHyvaCheckout\Api\Service\CheckoutSignupLineInterface;

/**
 * The newsletter sign-up line in Hyvä Checkout: unticked, shown only when the core module invites the email
 * address on the shopper's cart, checked again whenever an address of the cart changes
 *
 * The shopper's choice is stored on the cart; the core module signs the order's email address up after the order.
 */
class SignupLine extends Component
{
    /**
     * Whether the line shows
     *
     * @var bool
     */
    public bool $shown = false;

    /**
     * Whether the box is ticked
     *
     * @var bool
     */
    public bool $optIn = false;

    /** @var array<string, string> */
    protected $listeners = [
        'shipping_address_saved' => 'reevaluate',
        'shipping_address_activated' => 'reevaluate',
        'billing_address_saved' => 'reevaluate',
        'billing_address_activated' => 'reevaluate',
        'billing_as_shipping_address_updated' => 'reevaluate',
    ];

    /**
     * @param CheckoutSignupLineInterface $signupLine
     */
    public function __construct(
        private readonly CheckoutSignupLineInterface $signupLine
    ) {
    }

    /**
     * Magewire hook: decide the line when the component first renders
     *
     * @return void
     */
    public function mount(): void
    {
        $this->reevaluate();
    }

    /**
     * Decide the line for the email address now on the cart; a tick is dropped when that address is not invited
     *
     * @return void
     */
    public function reevaluate(): void
    {
        $this->shown = $this->signupLine->isShown();
        $optIn = $this->signupLine->isOptedIn();
        if ($optIn && !$this->shown) {
            $optIn = $this->signupLine->setOptIn(false);
        }
        $this->optIn = $optIn;
    }

    /**
     * Magewire hook for the checkbox: store the choice on the cart and show what was stored
     *
     * @param mixed $value
     * @return bool
     */
    public function updatedOptIn(mixed $value): bool
    {
        return $this->signupLine->setOptIn((bool)$value);
    }

    /**
     * Benefit text of the line
     *
     * @return string
     */
    public function getBenefitText(): string
    {
        return $this->signupLine->getBenefitText();
    }
}
