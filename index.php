<?php
// Hauptseite: Projektübersicht
// Tabellen-Auflistung: JZi
// Filtering und Sortierung: DSc

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

$benutzer = aktuellerBenutzer();
$pdo      = getDbConnection();

// Sortierung und Filterung auslesen 
$erlaubteSortierungen = ['name', 'abgabedatum', 'erstellt_am'];
$sortierung = in_array($_GET['sort'] ?? '', $erlaubteSortierungen)
    ? $_GET['sort']
    : 'erstellt_am';

$sortRichtung = ($_GET['richtung'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
$filterName   = trim($_GET['filter_name'] ?? '');

// Projekte aus der View laden (DSc: BE Views in HTML auflisten / DB View)
$query = 'SELECT * FROM v_projekte_uebersicht WHERE 1=1';
$params = [];

if (!empty($filterName)) {
    $query   .= ' AND name LIKE :filter_name';
    $params['filter_name'] = '%' . $filterName . '%';
}

$query .= ' ORDER BY ' . $sortierung . ' ' . $sortRichtung;

$stmt     = $pdo->prepare($query);
$stmt->execute($params);
$projekte = $stmt->fetchAll();

// Aufgaben des aktuellen Benutzers laden (JZi: FE Auflistung Tabelleninhalt)
$aufgabenSortierung = in_array($_GET['aufgaben_sort'] ?? '', ['abgabedatum', 'titel', 'erstellt_am'])
    ? $_GET['aufgaben_sort']
    : 'abgabedatum';

$aufgabenStmt = $pdo->prepare(
    'SELECT * FROM v_aufgaben_mit_benutzer
     WHERE zugewiesen_an = :benutzername
     ORDER BY ' . $aufgabenSortierung . ' ASC'
);
$aufgabenStmt->execute(['benutzername' => $benutzer['benutzername']]);
$meineAufgaben = $aufgabenStmt->fetchAll();

// Benutzer fuer Zuweisung laden
$benutzerListe = $pdo->query('SELECT id, benutzername FROM benutzer ORDER BY benutzername')->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Projektübersicht</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validierung.js"></script>
</head>
<body>

    <!-- Navigation -->
    <header class="header">
        <span class="benutzername"><?= htmlspecialchars($benutzer['benutzername']) ?></span>
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </header>

    <main class="hauptbereich">
        <?php flashAnzeigen(); ?>

        <!-- Projektübersicht -->
        <div class="seitentitel">
            <h1>Projektübersicht</h1>
            <button class="btn-neu" data-modal-oeffnen="modal-projekt-neu" title="Neues Projekt erstellen">&#x2295;</button>
        </div>

        <!-- Filterbereich (DSc: BE Filtering) -->
        <form method="GET" action="index.php" class="filter-bereich">
            <div class="formular-gruppe">
                <label for="filter_name">Projektname</label>
                <input type="text" id="filter_name" name="filter_name" placeholder="Suchen..." value="<?= htmlspecialchars($filterName) ?>">
            </div>
            <div class="formular-gruppe">
                <label for="sort">Sortieren nach</label>
                <select id="sort" name="sort">
                    <option value="erstellt_am" <?= $sortierung === 'erstellt_am' ? 'selected' : '' ?>>Erstellt am</option>
                    <option value="abgabedatum" <?= $sortierung === 'abgabedatum' ? 'selected' : '' ?>>Abgabedatum</option>
                    <option value="name"        <?= $sortierung === 'name'        ? 'selected' : '' ?>>Name</option>
                </select>
            </div>
            <div class="formular-gruppe">
                <label for="richtung">Reihenfolge</label>
                <select id="richtung" name="richtung">
                    <option value="asc"  <?= $sortRichtung === 'ASC'  ? 'selected' : '' ?>>Aufsteigend</option>
                    <option value="desc" <?= $sortRichtung === 'DESC' ? 'selected' : '' ?>>Absteigend</option>
                </select>
            </div>
            <div class="formular-gruppe">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-bearbeiten">Filtern</button>
            </div>
        </form>

        <!-- Projektkarten Grid (JZi: FE Auflistung) -->
        <div class="projekte-grid">
            <?php if (empty($projekte)): ?>
                <p>Keine Projekte gefunden. Erstelle dein erstes Projekt!</p>
            <?php else: ?>
                <?php foreach ($projekte as $projekt): ?>
                    <div class="projekt-karte">
                        <h2><?= htmlspecialchars($projekt['name']) ?></h2>
                        <div class="projekt-karte-inhalt">
                            <div class="projekt-bild-platzhalter"></div>
                            <div class="projekt-meta">
                                <div>Erstellt von: <?= htmlspecialchars($projekt['erstellt_von']) ?></div>
                                <div>Fällig am: <?= htmlspecialchars(date('j.n.Y', strtotime($projekt['abgabedatum']))) ?></div>
                                <div>Aufgaben: <?= (int) $projekt['anzahl_aufgaben'] ?></div>
                            </div>
                        </div>
                        <div class="projekt-aktionen">
                            <button
                                class="btn btn-bearbeiten"
                                data-modal-oeffnen="modal-projekt-bearbeiten-<?= (int) $projekt['id'] ?>"
                            >bearbeiten</button>
                            <a
                                href="/projekt-loeschen.php?id=<?= (int) $projekt['id'] ?>&csrf_token=<?= csrfToken() ?>"
                                class="btn btn-loeschen loeschen-btn"
                            >löschen</a>
                        </div>
                    </div>

                    <!-- Modal: Projekt bearbeiten (DSc: FE Auswahl fuer Aenderung) -->
                    <div class="modal-overlay" id="modal-projekt-bearbeiten-<?= (int) $projekt['id'] ?>">
                        <div class="modal">
                            <h2>Projekt bearbeiten</h2>
                            <?php
                            // Aufgaben dieses Projekts laden
                            $aufgabenProjekt = $pdo->prepare(
                                'SELECT a.*, b.benutzername AS zugewiesen_an_name
                                 FROM aufgaben a
                                 LEFT JOIN benutzer b ON a.zugewiesen_an = b.id
                                 WHERE a.projekt_id = :pid
                                 ORDER BY a.abgabedatum ASC'
                            );
                            $aufgabenProjekt->execute(['pid' => $projekt['id']]);
                            $projektAufgaben = $aufgabenProjekt->fetchAll();
                            ?>
                            <?php foreach ($projektAufgaben as $aufgabe): ?>
                                <form method="POST" action="aufgabe-bearbeiten.php" id="aufgabe-formular" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="aufgabe_id" value="<?= (int) $aufgabe['id'] ?>">
                                    <input type="hidden" name="projekt_id" value="<?= (int) $projekt['id'] ?>">

                                    <fieldset>
                                        <legend>Aufgabe</legend>

                                        <div class="formular-gruppe">
                                            <label for="aufgabe-titel-<?= $aufgabe['id'] ?>">Titel</label>
                                            <input
                                                type="text"
                                                id="aufgabe-titel-<?= $aufgabe['id'] ?>"
                                                name="titel"
                                                value="<?= htmlspecialchars($aufgabe['titel']) ?>"
                                            >
                                        </div>

                                        <div class="formular-gruppe">
                                            <label for="aufgabe-beschreibung-<?= $aufgabe['id'] ?>">Beschreibung</label>
                                            <textarea
                                                id="aufgabe-beschreibung-<?= $aufgabe['id'] ?>"
                                                name="beschreibung"
                                            ><?= htmlspecialchars($aufgabe['beschreibung'] ?? '') ?></textarea>
                                        </div>

                                        <div class="formular-gruppe">
                                            <label for="aufgabe-abgabedatum-<?= $aufgabe['id'] ?>">Abgabedatum</label>
                                            <input
                                                type="date"
                                                id="aufgabe-abgabedatum-<?= $aufgabe['id'] ?>"
                                                name="abgabedatum"
                                                value="<?= htmlspecialchars($aufgabe['abgabedatum']) ?>"
                                            >
                                        </div>

                                        <div class="formular-gruppe">
                                            <label for="aufgabe-erinnerung-<?= $aufgabe['id'] ?>">Erinnerung einstellen</label>
                                            <input
                                                type="datetime-local"
                                                id="aufgabe-erinnerung-<?= $aufgabe['id'] ?>"
                                                name="erinnerung_datum"
                                                value="<?= $aufgabe['erinnerung_datum'] ? date('Y-m-d\TH:i', strtotime($aufgabe['erinnerung_datum'])) : '' ?>"
                                            >
                                        </div>

                                        <div class="formular-gruppe">
                                            <div class="radio-gruppe">
                                                <span class="radio-gruppe-titel">Erinnerung per</span>
                                                <label>
                                                    <input type="radio" name="erinnerung_per" value="email"
                                                        <?= ($aufgabe['erinnerung_per'] === 'email' || $aufgabe['erinnerung_per'] === 'beide') ? 'checked' : '' ?>>
                                                    E-Mail
                                                </label>
                                                <label>
                                                    <input type="radio" name="erinnerung_per" value="push"
                                                        <?= ($aufgabe['erinnerung_per'] === 'push') ? 'checked' : '' ?>>
                                                    Push-Nachricht
                                                </label>
                                            </div>
                                        </div>

                                        <div class="formular-gruppe">
                                            <div class="checkbox-gruppe">
                                                <span class="checkbox-gruppe-titel">Zuweisungen</span>
                                                <?php foreach ($benutzerListe as $b): ?>
                                                    <label>
                                                        <input type="checkbox" name="zugewiesen_an[]" value="<?= (int) $b['id'] ?>"
                                                            <?= $aufgabe['zugewiesen_an'] == $b['id'] ? 'checked' : '' ?>>
                                                        <?= htmlspecialchars($b['benutzername']) ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <button type="submit" class="btn btn-primary">Speichern</button>
                                </form>
                            <?php endforeach; ?>

                            <p class="formular-link">
                                <a href="#" data-modal-schliessen="modal-projekt-bearbeiten-<?= (int) $projekt['id'] ?>">Schliessen</a>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Meine Aufgaben (JZi: Tabellenansicht) -->
        <section style="margin-top: 48px;">
            <div class="seitentitel">
                <h2 style="font-size:1.5rem; font-weight:900;">Meine Aufgaben</h2>
            </div>

            <div class="filter-bereich">
                <form method="GET" action="/index.php" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; width:100%;">
                    <?php if (!empty($filterName)): ?>
                        <input type="hidden" name="filter_name" value="<?= htmlspecialchars($filterName) ?>">
                    <?php endif; ?>
                    <div class="formular-gruppe">
                        <label for="aufgaben_sort">Sortieren nach</label>
                        <select id="aufgaben_sort" name="aufgaben_sort">
                            <option value="abgabedatum" <?= $aufgabenSortierung === 'abgabedatum' ? 'selected' : '' ?>>Abgabedatum</option>
                            <option value="titel"       <?= $aufgabenSortierung === 'titel'       ? 'selected' : '' ?>>Titel</option>
                            <option value="erstellt_am" <?= $aufgabenSortierung === 'erstellt_am' ? 'selected' : '' ?>>Erstellt am</option>
                        </select>
                    </div>
                    <div class="formular-gruppe">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-bearbeiten">Sortieren</button>
                    </div>
                </form>
            </div>

            <div class="tabelle-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><a href="?aufgaben_sort=titel">Titel</a></th>
                            <th><a href="?aufgaben_sort=abgabedatum">Fällig am</a></th>
                            <th>Projekt</th>
                            <th>Zugewiesen an</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($meineAufgaben)): ?>
                            <tr><td colspan="5">Keine Aufgaben zugewiesen.</td></tr>
                        <?php else: ?>
                            <?php foreach ($meineAufgaben as $aufgabe): ?>
                                <tr>
                                    <td><?= htmlspecialchars($aufgabe['titel']) ?></td>
                                    <td><?= htmlspecialchars(date('j.n.Y', strtotime($aufgabe['abgabedatum']))) ?></td>
                                    <td><?= htmlspecialchars($aufgabe['projekt_name']) ?></td>
                                    <td><?= htmlspecialchars($aufgabe['zugewiesen_an'] ?? '—') ?></td>
                                    <td>
                                        <a href="/aufgabe-loeschen.php?id=<?= (int) $aufgabe['id'] ?>&csrf_token=<?= csrfToken() ?>"
                                           class="btn btn-loeschen loeschen-btn" style="font-size:0.8rem; padding:4px 12px;">
                                           löschen
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal: Neues Projekt erstellen (DSc: FE Formular) -->
    <div class="modal-overlay" id="modal-projekt-neu">
        <div class="modal">
            <h2>Projekt erstellen</h2>
            <form id="projekt-formular" method="POST" action="/projekt-erstellen.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <fieldset>
                    <legend class="sr-only">Projektdaten</legend>

                    <div class="formular-gruppe">
                        <label for="projekt-name">Projektname</label>
                        <input type="text" id="projekt-name" name="name" placeholder="Projektname">
                    </div>

                    <div class="formular-gruppe">
                        <label for="projekt-beschreibung">Beschreibung</label>
                        <textarea id="projekt-beschreibung" name="beschreibung" placeholder="Beschreibung"></textarea>
                    </div>

                    <div class="formular-gruppe">
                        <label for="projekt-abgabedatum">Abgabedatum</label>
                        <input type="date" id="projekt-abgabedatum" name="abgabedatum" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>

                    <div class="formular-gruppe">
                        <div class="checkbox-gruppe">
                            <span class="checkbox-gruppe-titel">Zuweisungen</span>
                            <?php foreach ($benutzerListe as $b): ?>
                                <label>
                                    <input type="checkbox" name="zugewiesen_an[]" value="<?= (int) $b['id'] ?>">
                                    <?= htmlspecialchars($b['benutzername']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
            <p class="formular-link">
                <a href="#" data-modal-schliessen="modal-projekt-neu">Abbrechen</a>
            </p>
        </div>
    </div>

</body>
</html>
