<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validation côté serveur de toutes les saisies (la validation HTML/JS n'est
 * qu'un confort : elle peut être contournée, le serveur fait foi).
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function value(string $field): string
    {
        $v = $this->data[$field] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->errors[$field] = "Le champ « $label » est obligatoire.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        if ($this->value($field) !== '' && !filter_var($this->value($field), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse e-mail n'est pas valide.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        if (mb_strlen($this->value($field)) > $max) {
            $this->errors[$field] = "Le champ « $label » ne doit pas dépasser $max caractères.";
        }
        return $this;
    }

    public function phone(string $field): self
    {
        $v = preg_replace('/[\s.\-]/', '', $this->value($field));
        if ($v !== '' && !preg_match('/^(\+33|0)[1-9]\d{8}$/', $v)) {
            $this->errors[$field] = 'Le numéro de téléphone doit être un numéro français valide (ex. : 06 12 34 56 78).';
        }
        return $this;
    }

    public function postalCode(string $field): self
    {
        if ($this->value($field) !== '' && !preg_match('/^\d{5}$/', $this->value($field))) {
            $this->errors[$field] = 'Le code postal doit comporter 5 chiffres.';
        }
        return $this;
    }

    /** Politique de mot de passe : 10 caractères min., 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial. */
    public function password(string $field): self
    {
        if (!self::isStrongPassword($this->value($field))) {
            $this->errors[$field] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
        }
        return $this;
    }

    public static function isStrongPassword(string $p): bool
    {
        return mb_strlen($p) >= 10
            && preg_match('/[A-Z]/', $p)
            && preg_match('/[a-z]/', $p)
            && preg_match('/\d/', $p)
            && preg_match('/[^A-Za-z0-9]/', $p);
    }

    public function same(string $field, string $other, string $message): self
    {
        if ($this->value($field) !== $this->value($other)) {
            $this->errors[$other] = $message;
        }
        return $this;
    }

    public function intBetween(string $field, int $min, int $max, string $label): self
    {
        $v = filter_var($this->value($field), FILTER_VALIDATE_INT);
        if ($v === false || $v < $min || $v > $max) {
            $this->errors[$field] = "Le champ « $label » doit être un nombre entre $min et $max.";
        }
        return $this;
    }

    public function number(string $field, float $min, string $label): self
    {
        $v = filter_var(str_replace(',', '.', $this->value($field)), FILTER_VALIDATE_FLOAT);
        if ($v === false || $v < $min) {
            $this->errors[$field] = "Le champ « $label » doit être un nombre supérieur ou égal à $min.";
        }
        return $this;
    }

    public function date(string $field, string $label, bool $future = false): self
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->value($field));
        if (!$d || $d->format('Y-m-d') !== $this->value($field)) {
            $this->errors[$field] = "Le champ « $label » doit être une date valide.";
        } elseif ($future && $d < new \DateTimeImmutable('today')) {
            $this->errors[$field] = "La $label doit être dans le futur.";
        }
        return $this;
    }

    public function time(string $field, string $label): self
    {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', substr($this->value($field), 0, 5))) {
            $this->errors[$field] = "Le champ « $label » doit être une heure valide (HH:MM).";
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        if (!in_array($this->value($field), $allowed, true)) {
            $this->errors[$field] = "La valeur du champ « $label » n'est pas autorisée.";
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
