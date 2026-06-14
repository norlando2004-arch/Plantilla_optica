<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'correo_verificado_en' => now(),
            'contrasena' => static::$password ??= Hash::make('password'),
            'token_recordar' => Str::random(10),
            'rol' => 'cliente',
            'esta_activo' => true,
        ];
    }

    public function noVerificado(): static
    {
        return $this->state(fn (array $attributes) => [
            'correo_verificado_en' => null,
        ]);
    }
}
