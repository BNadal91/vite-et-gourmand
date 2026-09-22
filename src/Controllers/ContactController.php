<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Env;
use App\Core\Mailer;
use App\Core\Validator;
use App\Core\View;
use App\Models\Catalogue;

final class ContactController
{
    public function form(): void
    {
        View::render('pages/contact', ['titre' => 'Nous contacter', 'user' => Auth::user()]);
    }

    public function send(): void
    {
        // Pot de miel anti-robot : ce champ est invisible pour les humains
        if (!empty($_POST['site_web'])) {
            redirect('/contact');
        }
        $v = (new Validator($_POST))
            ->required('titre', 'Titre')->maxLength('titre', 150, 'Titre')
            ->required('description', 'Description')->maxLength('description', 3000, 'Description')
            ->required('email', 'Adresse e-mail')->email('email');
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/contact');
        }
        (new Catalogue())->enregistrerMessage($v->value('titre'), $v->value('description'), $v->value('email'));
        Mailer::send((string) Env::get('MAIL_CONTACT', 'contact@vite-gourmand.fr'), 'Nouvelle demande de contact : ' . $v->value('titre'), 'contact', [
            'titre' => $v->value('titre'), 'description' => $v->value('description'), 'email' => $v->value('email'),
        ]);
        clear_old();
        flash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les meilleurs délais.');
        redirect('/contact');
    }
}
