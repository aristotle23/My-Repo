<?php

namespace App\ThirdParty\IdentityProviders\Dto;

class GetBvnDetailsDto{
    public string $first_name;
    public string $last_name;
    public string $dob;
    public string $bvn;
    public string $raw_response;
    public string $raw_request;
}