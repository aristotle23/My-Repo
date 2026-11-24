<?php

namespace App\ThirdParty\IdentityProviders\Dto;

class GetBvnDetailsDto
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $bvn,
        public string $raw_response,
        public string $raw_request,
        public string $provider,
    ) {}
}
