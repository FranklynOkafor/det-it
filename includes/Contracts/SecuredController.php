<?php

namespace DetIt\Contracts;

/**
 * Interface SecuredController
 *
 * Defines a standard contract for controllers that require capability and nonce verification
 * prior to executing their action.
 */
interface SecuredController {
    /**
     * Authorize the incoming request.
     *
     * Implementations must verify:
     * - The user has the required capability.
     * - The request contains a valid nonce.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool;
}
