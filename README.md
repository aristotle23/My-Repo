<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('access_configs', function (Blueprint $table) {
            $table->id();
            $table->string("access_code")->unique();
            $table->boolean("is_active")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_configs');
    }
};




<?php

namespace App\Http\Middleware;

use App\Models\AccessConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessCode = $request->header("AccessCode");
        if(!$accessCode){
            return response()->json([
                "status" => false,
                "message" => "Unauthorise"
            ], 401);
        }
        $accessConfig = AccessConfig::where("access_code", $accessCode)->where("is_active", 1)->first();
        if(!$accessConfig){
            return response()->json(["status" => false,"message" => "Unauthorise"], 401);
        }
        return $next($request);
    }
}




public function getBvnDetails(array $request): string
    {
        $dbRecord = BvnLookupLog::getBvnRecord($request["bvn"]);
        if ( $dbRecord ) return $dbRecord;

        $providerRecord = $this->identityProvider->getBvnDetails(...$request);
        
        if (  !$providerRecord || strtolower($providerRecord->first_name) != strtolower($request["first_name"]) &&
                strtolower($providerRecord->last_name) != strtolower(request["last_name"])
        ) {
            throw new CustomException("Identity mismatch", 400);
        }
        
        $dbRecord = BvnLookupLog::create([
            "first_name" => $providerRecord->first_name,
            "last_name" => $providerRecord->last_name,
            "bvn" => $providerRecord->bvn,
            "bvn_hash" => $providerRecord->bvn,
            "raw_response" => $providerRecord->raw_response,
            "raw_request" => $providerRecord->raw_request,
            "provider" => $this->identityProvider->getProviderName()
        ]);
        $encryptedResponse = Crypt::encryptString(json_encode($dbRecord));
        //Cache::put(BvnLookupLog::getCacheKey($request["bvn"]), $encryptedResponse );
        return $encryptedResponse;
    }




<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bvn_lookup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->text('last_name');
            $table->text('bvn');
            $table->string('bvn_hash');
            $table->text('raw_response');
            $table->text('raw_request');
             $table->string('provider');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bvn_lookup_logs');
    }
};





    <?php

namespace App\Models;

use App\ThirdParty\IdentityProviders\Dto\GetBvnDetailsDto;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class BvnLookupLog extends Model
{
    protected $guarded = [];

    protected function bvn(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Crypt::encryptString($value),
        );
    }
    protected function bvnHash(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => hash_hmac(
                "sha256",
                $value,
                config("app.key"),
            ),
        );
    }
    protected function rawResponse(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Crypt::encryptString($value),
        );
    }
    public static function getBvnRecord(string $bvn): string | null
    {
        //$record = Cache::get(self::getCacheKey($bvn));
        //if($record) return $record;
        $record = self::where("bvn_hash", hash_hmac("sha256", $bvn,  config("app.key")))->first();
        if (!$record) {
            return null;
        }
        return Crypt::encryptString(json_encode($record));
    }
    public static function getCacheKey(string $bvn): string
    {
        return "bvn:lookup:" . $bvn;
    }
}



<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('class_path');
            $table->boolean('is_active')->default(0);
            $table->bigInteger('ttl_success')->default(0);
            $table->bigInteger('ttl_failed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};


<?php

namespace App\Providers;

use App\Models\Provider;
use App\ThirdParty\IdentityProviders\Contract\ProviderContract;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(ProviderContract::class, function(Application $app) {
            $provider = Provider::where("is_active", 1)->first();
            return $app->make($provider->class_path);
        });
    }
}
