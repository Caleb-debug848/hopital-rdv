<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Secretary\SecretaryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Accueil : Redirige vers l'espace de travail si connecté, sinon affiche directement la page d'authentification
Route::get('/', function () {
    if (Auth::check()) {
        return AuthController::redirectByRole(Auth::user());
    }
    return view('auth.login');
})->name('home');

// Authentification & Connexion Standard
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick.login');

// Notifications (pour tout utilisateur connecté)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
});

// Espace Patient
Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'dashboard'])->name('dashboard');
    Route::get('/rendez-vous', [AppointmentController::class, 'index'])->name('rendez-vous.index');
    Route::get('/rendez-vous/nouveau', [AppointmentController::class, 'create'])->name('rendez-vous.create');
    Route::get('/api/slots', [AppointmentController::class, 'getSlots'])->name('rendez-vous.slots');
    Route::post('/rendez-vous', [AppointmentController::class, 'store'])->name('rendez-vous.store');
    Route::get('/rendez-vous/{rendezVous}', [AppointmentController::class, 'show'])->name('rendez-vous.show');
    Route::get('/rendez-vous/{rendezVous}/attestation', [AppointmentController::class, 'attestation'])->name('rendez-vous.attestation');
    Route::post('/rendez-vous/{rendezVous}/rappel', [AppointmentController::class, 'sendReminder'])->name('rendez-vous.rappel');
    Route::post('/rendez-vous/{rendezVous}/annuler', [AppointmentController::class, 'cancel'])->name('rendez-vous.cancel');
    Route::get('/historique/export', [AppointmentController::class, 'exportHistorique'])->name('historique.export');
    Route::post('/liste-attente', [AppointmentController::class, 'joinWaitingList'])->name('rendez-vous.waiting-list');
});

// Espace Médecin
Route::middleware(['auth', 'role:medecin'])->prefix('medecin')->name('medecin.')->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/planning', [DoctorController::class, 'planning'])->name('planning');
    Route::post('/rendez-vous/{rendezVous}/statut', [DoctorController::class, 'updateStatus'])->name('rdv.status');
    Route::post('/indisponibilites', [DoctorController::class, 'storeIndisponibilite'])->name('indisponibilite.store');
});

// Espace Secrétariat / Guichet
Route::middleware(['auth', 'role:secretaire,admin'])->prefix('secretaire')->name('secretaire.')->group(function () {
    Route::get('/guichet', [SecretaryController::class, 'guichet'])->name('guichet');
    Route::post('/rendez-vous/{rendezVous}/statut', [SecretaryController::class, 'updateStatus'])->name('rdv.status');
    Route::post('/rendez-vous/guichet', [SecretaryController::class, 'createAppointmentDesk'])->name('rdv.create_desk');
});

// Espace Administrateur
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/statistiques', [AdminController::class, 'statistiques'])->name('statistiques');
    Route::get('/statistiques/export', [AdminController::class, 'exportStatistiquesCsv'])->name('statistiques.export');
    Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index');
    Route::post('/utilisateurs', [UserController::class, 'store'])->name('users.store');
    Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/medecins', [AdminController::class, 'medecins'])->name('medecins.index');
    Route::post('/medecins', [AdminController::class, 'storeMedecin'])->name('medecins.store');
    Route::put('/medecins/{medecin}', [AdminController::class, 'updateMedecin'])->name('medecins.update');
    Route::delete('/medecins/{medecin}', [AdminController::class, 'destroyMedecin'])->name('medecins.destroy');
    Route::post('/medecins/{medecin}/toggle', [AdminController::class, 'toggleMedecinStatut'])->name('medecins.toggle');
    Route::get('/specialites', [AdminController::class, 'specialites'])->name('specialites.index');
    Route::post('/specialites', [AdminController::class, 'storeSpecialite'])->name('specialites.store');
    Route::put('/specialites/{specialite}', [AdminController::class, 'updateSpecialite'])->name('specialites.update');
    Route::delete('/specialites/{specialite}', [AdminController::class, 'destroySpecialite'])->name('specialites.destroy');
    Route::get('/patients', [AdminController::class, 'patients'])->name('patients.index');
    Route::get('/patients/{patient}', [AdminController::class, 'patientShow'])->name('patients.show');
    Route::get('/rendez-vous/{rendezVous}/attestation', [AdminController::class, 'attestation'])->name('rendez-vous.attestation');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit_logs.index');
    Route::get('/parametres', [AdminController::class, 'parametres'])->name('parametres.index');
    Route::post('/parametres', [AdminController::class, 'updateParametres'])->name('parametres.update');
    Route::get('/cabinets', [AdminController::class, 'cabinets'])->name('cabinets.index');
    Route::post('/cabinets', [AdminController::class, 'storeCabinet'])->name('cabinets.store');
    Route::put('/cabinets/{cabinet}', [AdminController::class, 'updateCabinet'])->name('cabinets.update');
    Route::delete('/cabinets/{cabinet}', [AdminController::class, 'destroyCabinet'])->name('cabinets.destroy');
    Route::get('/rappels', [AdminController::class, 'rappels'])->name('rappels.index');
    Route::post('/rappels/trigger', [AdminController::class, 'triggerRappelsNow'])->name('rappels.trigger');
});





