<?php

namespace Filament\Pages\Auth;

use App\Models\SessionCaisse;
use App\Models\User;
use App\Models\DbaUser;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\SimplePage;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

/**
 * @property Form $form
 */
class OracleLogin extends Login
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    const OPEN = 'OPEN';

    const LOCKED = 'LOCKED';

    const EXPIREDLOCKED = 'LOCKED(TIMED)';

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::pages.auth.login';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {



        // $connString = "(DESCRIPTION =(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.60.34)(PORT = 5500))) (CONNECT_DATA = (SERVICE_NAME = dbpost)))";

        // $authenticationLimit = config('app.LOGIN_LIMIT', 4);

        $data = $this->form->getState();

        // oci_connect(strtoupper($data['username']), $data['password'],  $connString);
 
        $oracleuser = DbaUser::where("username", strtoupper($data["username"]))->first();


        try {

            $this->rateLimit(5); 

            try {

                $numeroCaisse = DB::table("caissier")->whereRaw("rtrim(nom_caissier) = rtrim(?)", [ $oracleuser->name])->first();
                 

                $conn = oci_connect(strtoupper($data['username']), $data['password'],  env("CONNECTION"));

                if($numeroCaisse != null)
                {
                    SessionCaisse::create([
                        "numero_caisse" =>  $numeroCaisse->numero_caisse ,
                        "numero_caissier" =>  $numeroCaisse->numero_caissier,
                        "date_session" => today() ,
                        "utilisateur" => strtoupper($data['username']),
                        "numero_session" =>  SessionCaisse::orderBy("numero_session", 'desc')->first()->numero_session ,
                    ]);

                }
              
            } catch (\ErrorException $e) {

                if ($oracleuser) {
                    if (in_array($oracleuser->account_status, [self::LOCKED, self::EXPIREDLOCKED])) {
                        throw ValidationException::withMessages(['username' => 'Votre compte est bloqué, veuiller contactez votre administrateur.']);
                    }
                }
                
                $this->throwFailureValidationException();
                
            }

        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }


        $userToLogIn = User::where('username', $data['username'])->first();
        
        if (!$userToLogIn) {

            $createUser = User::create([
                'email' => Str::random(100).'@laposte.tg',
                'password' => Hash::make('L@poste+2024'),
                'name' => $data['username'],
                'username' => $data['username'],
            ]);

            $newUser = User::where("name", $data['username'])->first();

            Auth::login($newUser);

        } else {

            Auth::login($userToLogIn);

        }

        session()->regenerate();



        return app(LoginResponse::class);
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getUserNameFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getUserNameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__("Nom d'utilisateur"))
            ->required()
            ->autocomplete();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('Mot de passe'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Hidden::make('remember')
            ->default(true);
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label(__('filament-panels::pages/auth/login.actions.register.label'))
            ->url(filament()->getRegistrationUrl());
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-panels::pages/auth/login.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-panels::pages/auth/login.heading');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('Nom d\'utilisateur ou mot de passe incorrect'),
        ]);
    }

    protected function OracleUserNotAllowedOnAppValidation(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('Vous n\'avez pas acces à cette application'),
        ]);
    }
}
