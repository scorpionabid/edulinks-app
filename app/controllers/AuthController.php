<?php
/**
 * EduLinks Authentication Controller
 * 
 * Handles user authentication and login/logout
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Logger;

class AuthController extends Controller
{
    /**
     * Handle login attempt
     */
    public function login(): void
    {
        // Verify CSRF token
        if (!CSRF::verify()) {
            Session::error('Təhlükəsizlik xətası. Yenidən cəhd edin.');
            $this->redirect('/login');
            return;
        }
        
        $email = $this->input('email');
        $password = $this->input('password');
        $remember = (bool)$this->input('remember');
        
        // Validate input
        $validation = $this->validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required|min:1'
        ]);
        
        if (!$validation['valid']) {
            Session::error('Email və şifrə sahələri mütləqdir.');
            $this->redirect('/login');
            return;
        }
        
        // Attempt authentication
        if (Auth::attempt($email, $password, $remember)) {
            $user = Auth::user();
            
            Logger::logAuth('login', $email, true);
            Session::success("Xoş gəldiniz, {$user['first_name']}!");
            
            // Redirect based on role
            if (Auth::isAdmin()) {
                $this->redirect('/admin');
            } else {
                $this->redirect('/user');
            }
        } else {
            Logger::logAuth('login_failed', $email, false);
            Session::error('Email və ya şifrə yanlışdır.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Handle logout
     */
    public function logout(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            Logger::logAuth('logout', $user['email'], true);
        }
        
        Auth::logout();
        Session::success('Uğurla çıxış etdiniz.');
        $this->redirect('/login');
    }
}