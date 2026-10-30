public function ninLookup($nin):array{
        $response = $this->httpClient->post('/v3/lookup/nin', [
            'nin' => $nin
        ]);
        if($response->status() !== 200 || $response->json('status') !== 'successful'){
            Log::info('Mono NIN lookup failed with Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to retrieve BVN details');
        }
        return $response->json('data');
}

---- service

public function ninLookup(string $nin){
        
        $nin_record = UserRepository::getNIN($nin);
        if ($nin_record) {
            return ['success' => true, 'message' => 'Pre validated', 'data' => $nin_record, 'status_code' => 200];
        }
        try {
            $validateData = $this->mono->ninLookup($nin);
        } catch (\Exception $e) {
            return response(['success' => false, 'message' =>  $e->getMessage()], 400);
        }
        $validateData['provider'] = 'Mono';
        $nin_record = UserRepository::logValidatedNIN($validateData); //create a job to handle this
        return ['success' => true, 'message' => 'NIN validated successfully', 'data' => $nin_record, 'status_code' => 200];
    }