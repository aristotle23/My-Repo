<?php

namespace App\ThirdParty\IdentityProviders\Contract;

use App\ThirdParty\IdentityProviders\Dto\GetBvnDetailsDto;

interface ProviderContract {
    public function getBvnDetails(string ...$param): GetBvnDetailsDto;
    public function getProviderName(): string;
}