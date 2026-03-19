// Frontend Validierung und Zwischenspeicherung fuer Formulare
// Validierungsfunktionen: JZi
// Zwischenspeicherung (sessionStorage): DSc

'use strict';

// =============================================
// Validierungsfunktionen (JZi)
// =============================================

/**
 * Prueft ob ein Wert leer ist
 */
function istLeer(wert) {
    return wert.trim() === '';
}

/**
 * Prueft ob eine E-Mail Adresse gueltig ist
 */
function istGueltigeEmail(email) {
    const muster = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return muster.test(email);
}

/**
 * Prueft ob ein Passwort die Mindestanforderungen erfuellt
 * Mindestens 8 Zeichen
 */
function istGueltigesPasswort(passwort) {
    return passwort.length >= 8;
}

/**
 * Zeigt einen Fehler bei einem Eingabefeld an
 */
function fehlerAnzeigen(inputElement, nachricht) {
    inputElement.classList.add('fehler');
    let fehlerSpan = inputElement.parentElement.querySelector('.fehler-text');
    if (!fehlerSpan) {
        fehlerSpan = document.createElement('span');
        fehlerSpan.className = 'fehler-text';
        inputElement.parentElement.appendChild(fehlerSpan);
    }
    fehlerSpan.textContent = nachricht;
}

/**
 * Entfernt den Fehler bei einem Eingabefeld
 */
function fehlerEntfernen(inputElement) {
    inputElement.classList.remove('fehler');
    const fehlerSpan = inputElement.parentElement.querySelector('.fehler-text');
    if (fehlerSpan) {
        fehlerSpan.remove();
    }
}

/**
 * Validiert das Login-Formular
 */
function loginValidieren(formular) {
    let gueltig = true;
    const benutzerInput  = formular.querySelector('#login-benutzer');
    const passwortInput  = formular.querySelector('#login-passwort');

    fehlerEntfernen(benutzerInput);
    fehlerEntfernen(passwortInput);

    if (istLeer(benutzerInput.value)) {
        fehlerAnzeigen(benutzerInput, 'Benutzername oder E-Mail darf nicht leer sein.');
        gueltig = false;
    }

    if (istLeer(passwortInput.value)) {
        fehlerAnzeigen(passwortInput, 'Passwort darf nicht leer sein.');
        gueltig = false;
    }

    return gueltig;
}

/**
 * Validiert das Registrierungsformular
 */
function registrierenValidieren(formular) {
    let gueltig = true;
    const emailInput      = formular.querySelector('#reg-email');
    const benutzerInput   = formular.querySelector('#reg-benutzername');
    const passwortInput   = formular.querySelector('#reg-passwort');
    const bestaetigInput  = formular.querySelector('#reg-passwort-bestaetigung');

    [emailInput, benutzerInput, passwortInput, bestaetigInput].forEach(fehlerEntfernen);

    if (!istGueltigeEmail(emailInput.value)) {
        fehlerAnzeigen(emailInput, 'Bitte eine gültige E-Mail Adresse eingeben.');
        gueltig = false;
    }

    if (istLeer(benutzerInput.value)) {
        fehlerAnzeigen(benutzerInput, 'Benutzername darf nicht leer sein.');
        gueltig = false;
    }

    if (!istGueltigesPasswort(passwortInput.value)) {
        fehlerAnzeigen(passwortInput, 'Passwort muss mindestens 8 Zeichen lang sein.');
        gueltig = false;
    }

    if (passwortInput.value !== bestaetigInput.value) {
        fehlerAnzeigen(bestaetigInput, 'Passwörter stimmen nicht überein.');
        gueltig = false;
    }

    return gueltig;
}

/**
 * Validiert das Projekt-Formular
 */
function projektValidieren(formular) {
    let gueltig = true;
    const nameInput  = formular.querySelector('#projekt-name');
    const datumInput = formular.querySelector('#projekt-abgabedatum');

    [nameInput, datumInput].forEach(fehlerEntfernen);

    if (istLeer(nameInput.value)) {
        fehlerAnzeigen(nameInput, 'Projektname darf nicht leer sein.');
        gueltig = false;
    }

    if (istLeer(datumInput.value)) {
        fehlerAnzeigen(datumInput, 'Abgabedatum muss angegeben werden.');
        gueltig = false;
    }

    return gueltig;
}

/**
 * Validiert das Aufgaben-Formular
 */
function aufgabeValidieren(formular) {
    let gueltig = true;
    const titelInput = formular.querySelector('#aufgabe-titel');
    const datumInput = formular.querySelector('#aufgabe-abgabedatum');

    [titelInput, datumInput].forEach(fehlerEntfernen);

    if (istLeer(titelInput.value)) {
        fehlerAnzeigen(titelInput, 'Titel darf nicht leer sein.');
        gueltig = false;
    }

    if (istLeer(datumInput.value)) {
        fehlerAnzeigen(datumInput, 'Abgabedatum muss angegeben werden.');
        gueltig = false;
    }

    return gueltig;
}

// =============================================
// Zwischenspeicherung Formulardaten (DSc)
// Daten werden im sessionStorage gespeichert damit
// bei einem Validierungsfehler die Eingaben erhalten bleiben
// =============================================

const SPEICHER_KEY_PROJEKT  = 'formular_projekt';
const SPEICHER_KEY_AUFGABE  = 'formular_aufgabe';

/**
 * Speichert alle Werte eines Formulars im sessionStorage
 */
function formularZwischenspeichern(formular, schluessel) {
    const daten = {};
    const felder = formular.querySelectorAll('input, textarea, select');
    felder.forEach(feld => {
        if (feld.name) {
            if (feld.type === 'checkbox' || feld.type === 'radio') {
                if (feld.checked) {
                    daten[feld.name] = feld.value;
                }
            } else {
                daten[feld.name] = feld.value;
            }
        }
    });
    sessionStorage.setItem(schluessel, JSON.stringify(daten));
}

/**
 * Stellt gespeicherte Formulardaten wieder her
 */
function formularWiederHerstellen(formular, schluessel) {
    const gespeichert = sessionStorage.getItem(schluessel);
    if (!gespeichert) return;

    const daten = JSON.parse(gespeichert);
    const felder = formular.querySelectorAll('input, textarea, select');

    felder.forEach(feld => {
        if (feld.name && daten[feld.name] !== undefined) {
            if (feld.type === 'checkbox' || feld.type === 'radio') {
                feld.checked = feld.value === daten[feld.name];
            } else {
                feld.value = daten[feld.name];
            }
        }
    });
}

/**
 * Loescht den Zwischenspeicher fuer ein Formular
 */
function formularSpeicherLeeren(schluessel) {
    sessionStorage.removeItem(schluessel);
}

// =============================================
// Initialisierung wenn Seite geladen ist
// =============================================

document.addEventListener('DOMContentLoaded', function () {

    // Login-Formular
    const loginFormular = document.getElementById('login-formular');
    if (loginFormular) {
        loginFormular.addEventListener('submit', function (e) {
            if (!loginValidieren(this)) {
                e.preventDefault();
            }
        });
    }

    // Registrierungsformular
    const regFormular = document.getElementById('registrieren-formular');
    if (regFormular) {
        regFormular.addEventListener('submit', function (e) {
            if (!registrierenValidieren(this)) {
                e.preventDefault();
            }
        });
    }

    // Projekt-Formular mit Zwischenspeicherung
    const projektFormular = document.getElementById('projekt-formular');
    if (projektFormular) {
        // Gespeicherte Daten wiederherstellen wenn vorhanden
        formularWiederHerstellen(projektFormular, SPEICHER_KEY_PROJEKT);

        // Bei jeder Eingabe zwischenspeichern
        projektFormular.addEventListener('input', function () {
            formularZwischenspeichern(this, SPEICHER_KEY_PROJEKT);
        });

        projektFormular.addEventListener('submit', function (e) {
            if (!projektValidieren(this)) {
                e.preventDefault();
            } else {
                // Erfolgreich abgesendet also Speicher leeren
                formularSpeicherLeeren(SPEICHER_KEY_PROJEKT);
            }
        });
    }

    // Aufgaben-Formular mit Zwischenspeicherung
    const aufgabeFormular = document.getElementById('aufgabe-formular');
    if (aufgabeFormular) {
        formularWiederHerstellen(aufgabeFormular, SPEICHER_KEY_AUFGABE);

        aufgabeFormular.addEventListener('input', function () {
            formularZwischenspeichern(this, SPEICHER_KEY_AUFGABE);
        });

        aufgabeFormular.addEventListener('submit', function (e) {
            if (!aufgabeValidieren(this)) {
                e.preventDefault();
            } else {
                formularSpeicherLeeren(SPEICHER_KEY_AUFGABE);
            }
        });
    }

    // Modal oeffnen/schliessen
    document.querySelectorAll('[data-modal-oeffnen]').forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = this.dataset.modalOeffnen;
            const modal   = document.getElementById(modalId);
            if (modal) modal.classList.add('aktiv');
        });
    });

    document.querySelectorAll('[data-modal-schliessen]').forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = this.dataset.modalSchliessen;
            const modal   = document.getElementById(modalId);
            if (modal) modal.classList.remove('aktiv');
        });
    });

    // Modal schliessen wenn auf Hintergrund geklickt wird
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('aktiv');
            }
        });
    });

    // Loeschen-Bestaetigung
    document.querySelectorAll('.loeschen-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Wirklich loeschen? Diese Aktion kann nicht rueckgaengig gemacht werden.')) {
                e.preventDefault();
            }
        });
    });
});
