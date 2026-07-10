# Module Vue : Gabarits Principaux (Layouts)

Ce répertoire contient les gabarits maîtres de l'application CakePHP.

## Architecture UI (Holy Grail Layout & Flexbox)
Pour répondre aux exigences des interfaces modernes embarquant de lourdes grilles de données (Tabulator), le gabarit `default.php` est structuré comme une Single Page Application (SPA).

**Principes de confinement :**
1. La balise `<body>` et le `<main>` sont verrouillés sur la hauteur exacte de l'écran (`vh-100`) et interdisent tout débordement global (`overflow-hidden`).
2. L'espace interne est distribué via **Flexbox** (`d-flex flex-column flex-grow-1`).
3. Les composants internes (comme les grilles Tabulator) s'ajustent automatiquement à l'espace disponible en utilisant la propriété CSS `height: 100%`. Le défilement (scroll) s'effectue exclusivement à l'intérieur des composants de données, empêchant ainsi la disparition de la barre de navigation.
