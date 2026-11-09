<?php
// Copier ce fichier en config.php et renseigner le jeton (Nyblit → Mon compte → Accès API).
// Scopes conseillés : tasks:read, tasks:write, projects:read, projects:write, workflows:read
return [
  'token'    => 'nyb_pat_VOTRE_JETON',
  // Selon la doc : https://api.nyblit.app/v1  (l'OpenAPI indique https://developers.nyblit.app/api/v1)
  'base_url' => 'https://api.nyblit.app/v1',
];
