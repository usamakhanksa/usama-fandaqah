<?php

namespace Laravel\Spark {
    class User extends \Illuminate\Foundation\Auth\User {}
    class Team extends \Illuminate\Database\Eloquent\Model {}
    trait CanJoinTeams {
        public function currentTeam() { return null; }
        public function teams() { return $this->belongsToMany(Team::class, 'team_users'); }
    }
    class Spark {
        public static function interact($class, $args) {}
        public static function useStripe() { return new self; }
        public static function noCardUpFront() { return new self; }
        public static function teamTrialDays($days) { return new self; }
        public static function teamPlan($name, $slug) { return new self; }
        public static function price($price) { return new self; }
        public static function yearly() { return new self; }
        public static function useDefaultRole($role) { return new self; }
        public static function prefixTeamsAs($prefix) { return new self; }
        public static function ensureEmailIsVerified() { return new self; }
        public static function user() { return new self; }
        public function findOrFail($id) { return \App\User::findOrFail($id); }
        public static function afterLoginRedirect() { return '/home'; }
    }
    class Subscription extends \Illuminate\Database\Eloquent\Model {}
    class TeamSubscription extends \Illuminate\Database\Eloquent\Model {}
}

namespace Laravel\Spark\Contracts\Interactions\Settings\Teams {
    interface AddTeamMember {}
}

namespace Laravel\Spark\Providers {
    class AppServiceProvider extends \Illuminate\Support\ServiceProvider {
        public function boot() {}
        public function register() {}
    }
}

namespace Laravel\Spark\Contracts\Repositories {
    interface TeamRepository {}
}

namespace Laravel\Spark\Notifications {
    class SparkChannel {}
    class SparkNotification {}
}

namespace Laravel\Spark\Events\Teams\Subscription {
    class TeamSubscribed {}
}

namespace Laravel\Spark\Http\Middleware {
    class CreateFreshApiToken { public function handle($request, $next) { return $next($request); } }
    class VerifyUserIsDeveloper { public function handle($request, $next) { return $next($request); } }
    class VerifyUserHasTeam { public function handle($request, $next) { return $next($request); } }
    class VerifyUserIsSubscribed { public function handle($request, $next) { return $next($request); } }
    class VerifyTeamIsSubscribed { public function handle($request, $next) { return $next($request); } }
}

namespace Laravelista\Comments {
    trait Commenter {
        public function comments() { return $this->morphMany('Laravelista\Comments\Comment', 'commenter'); }
    }
}

namespace ChrisWare\NovaBreadcrumbs\Traits { trait Breadcrumbs {} }
namespace DanielDeWit\NovaSingleRecordResource\Traits { trait SupportSingleRecordNavigationLinks {} }
namespace DanielDeWit\NovaSingleRecordResource\Contracts { interface SingleRecordResourceInterface {} }

namespace Spatie\Activitylog\Traits {
    trait DetectsChanges {
        public function attributeValuesToBeLogged(string $processingEvent): array { return []; }
    }
}
