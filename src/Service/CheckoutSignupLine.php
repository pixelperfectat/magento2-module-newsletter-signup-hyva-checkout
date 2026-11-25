<?php declare(strict_types=1);

namespace PixelPerfect\NewsletterSignupHyvaCheckout\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PixelPerfect\NewsletterSignup\Api\Service\CheckoutOptInInterface;
use PixelPerfect\NewsletterSignup\Api\Service\CheckoutSignupInterface;
use PixelPerfect\NewsletterSignupHyvaCheckout\Api\Service\CheckoutSignupLineInterface;
use Psr\Log\LoggerInterface;

class CheckoutSignupLine implements CheckoutSignupLineInterface
{
    /**
     * The core module's settings of the checkout sign-up line, shared with the Luma checkout
     */
    public const XML_PATH_NEWSLETTER_ACTIVE = 'newsletter/general/active';
    public const XML_PATH_ENABLED = 'newsletter/signup_checkout/enabled';
    public const XML_PATH_BENEFIT_TEXT = 'newsletter/signup_checkout/benefit_text';

    /**
     * @param CheckoutSession $checkoutSession
     * @param CheckoutSignupInterface $checkoutSignup
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly CheckoutSignupInterface $checkoutSignup,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isShown(): bool
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_NEWSLETTER_ACTIVE, ScopeInterface::SCOPE_STORE)
            || !$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)
        ) {
            return false;
        }
        try {
            $cartId = $this->getCartId();
            return $cartId > 0 && $this->checkoutSignup->isInvited($cartId);
        } catch (\Throwable $e) {
            $this->logError('the line could not be decided', $e);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isOptedIn(): bool
    {
        try {
            return (bool)$this->checkoutSession->getQuote()->getData(CheckoutOptInInterface::QUOTE_FIELD);
        } catch (\Throwable $e) {
            $this->logError('the stored choice could not be read', $e);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function setOptIn(bool $optIn): bool
    {
        try {
            $cartId = $this->getCartId();
            return $cartId > 0 && $this->checkoutSignup->setOptIn($cartId, $optIn);
        } catch (\Throwable $e) {
            $this->logError('the choice could not be stored', $e);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getBenefitText(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_BENEFIT_TEXT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Id of the shopper's cart, 0 when there is none yet
     *
     * @return int
     */
    private function getCartId(): int
    {
        return (int)$this->checkoutSession->getQuote()->getId();
    }

    /**
     * Log a failure of the line; the checkout goes on without it
     *
     * @param string $what
     * @param \Throwable $e
     * @return void
     */
    private function logError(string $what, \Throwable $e): void
    {
        $this->logger->error(
            'Newsletter sign-up in Hyvä Checkout: ' . $what . ': ' . $e->getMessage(),
            ['exception' => $e]
        );
    }
}
