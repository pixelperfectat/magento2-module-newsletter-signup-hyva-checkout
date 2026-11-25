<?php declare(strict_types=1);

namespace PixelPerfect\NewsletterSignupHyvaCheckout\Api\Service;

/**
 * The newsletter sign-up line of the current shopper's Hyvä Checkout cart
 *
 * Answers only for the email address already on the shopper's own cart, through the core module's checkout
 * sign-up; never for an address passed in.
 *
 * @api
 */
interface CheckoutSignupLineInterface
{
    /**
     * Whether the line shows: the newsletter and the line are on, and core invites the cart's email address
     *
     * @return bool
     */
    public function isShown(): bool;

    /**
     * Whether the shopper's ticked choice is stored on the cart
     *
     * @return bool
     */
    public function isOptedIn(): bool;

    /**
     * Store the shopper's choice on the cart; a tick is refused when the cart's email address is not invited
     *
     * @param bool $optIn
     * @return bool The stored choice
     */
    public function setOptIn(bool $optIn): bool;

    /**
     * Benefit text of the line for the current store view
     *
     * @return string
     */
    public function getBenefitText(): string;
}
