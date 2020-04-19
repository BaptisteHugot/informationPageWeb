# informationPageWeb
Cette API permet d'extraire les informations suivantes concernant une URL ou une adresse IP entrée préalablement par un utilisateur.

Ce dépôt contient également un dossier (examples) servant d'exemple d'utilisation de l'API en PHP, qui contient lui-même trois sous-dossiers :
* cronCheck, permettant d'effectuer un Cron mettant à jour la table contenant la liste des serveurs des domaines déjà testés et dont le dernier test remonte à plus d'une semaine et de mettre à jour les tables nécessaires pour afficher les graphiques présents dans le dossier statistics
* fileUpload, permettant de faire des tests sur les serveurs de plusieurs domaines en même temps à l'aide d'un fichier au format .csv préalablement rempli (un exemple de tel fichier est disponible dans le sous-dossier)
* statistics, permettant d'afficher des statistiques sous forme de graphes sur le nombre de sites testés et la qualité des sites testés

## Utilisation
* Les informations sur les serveurs du domaine (adresses IPv4 et IPv6), à l'aide du mot clé SERVER suivi d'une adresse d'un site Internet
* Le Whois du domaine, à l'aide du mot clé WHOIS suivi d'une adresse d'un site Internet
* Les entêtes HTTP de la page, à l'aide du mot clé HEADER suivi d'une adresse d'un site Internet
* Les balises meta de la page, à l'aide du mot clé META suivi d'une adresse d'un site Internet
* Les adresses IP de l'utilisateur (IPv4 et IPv6), à l'aide du mot clé USERIP
* Le User Agent du navigateur de l'utilisateur, à l'aide du mot clé USERAGENT
* Les informations sur le nom du domaine, à l'aide du mot clé HOSTIP suivi d'une adresse IP (au format IPv4 ou IPv6)
* Le ping d'une adresse IP ou d'une adresse d'un site Internet, à l'aide du mot clé PINGHOST suivi d'une adresse IP (au format IPv4 ou IPv6) ou d'une adresse d'un site Internet
* Le ping d'une adresse IP ou d'une adresse d'un site Internet, avec un port précisé, à l'aide des mots clés PINGHOST et PINGPORT suivis respectivement d'une adresse IP (au format IPv4 ou IPv6) ou d'une adresse d'un site Internet et d'un numéro de port

## Ecrit avec
* [PHP](https://secure.php.net/) - Le langage de programmation utilisé pour récupérer les informations pertinentes
* [HTML](https://www.w3.org/html/) - Le langage de programmation utilisé pour afficher la page Internet
* [CSS](https://www.w3.org/Style/CSS/) - Le langage de programmation utilisé pour gérer les styles de la page Internet
* [SQL](https://www.iso.org/standard/63555.html) - Le langage de programmation utilisé pour stocker les éléments dans une base de données
* [Javascript](https://www.ecma-international.org/publications/standards/Ecma-262.htm) - Le langage de programmation utilisé pour gérer une partie des styles

## Bibliothèques utilisées
* [jQuery](https://jquery.com/) - La bibliothèque utilisée pour gérer une partie des styles
* [Chart.js](https://www.chartjs.org/) - La bibliothèque utilisée pour afficher des graphiques
* [ipify](https://www.ipify.org/) - La bibliothèque utilisée pour connaître les adresses IP de l'utilisateur
* [php-whois](https://github.com/regru/php-whois/blob/master/src/Phois/Whois/whois.servers.json) - Contient la liste des serveurs utilisés par les registrars

## Versions
[SemVer](http://semver.org/) est utilisé pour la gestion de versions. Pour connaître les versions disponibles, veuillez vous référer aux [étiquettes disponibles dans ce dépôt](https://github.com/BaptisteHugot/informationPageWeb/releases/).

## Auteurs
* **Baptiste Hugot** - *Travail initial* - [BaptisteHugot](https://github.com/BaptisteHugot)

## Licence
Ce projet est disponible sous licence logiciel MIT. Veuillez lire le fichier [LICENSE](LICENSE) pour plus de détails.

## Règles de conduite
Pour connaître l'ensemble des règles de conduite à respecter sur ce dépôt, veuillez lire le fichier [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## Contribution au projet
Si vous souhaitez contribuer au projet, que ce soit en corrigeant des bogues ou en proposant de nouvelles fonctionnalités, veuillez lire le fichier [CONTRIBUTING.md](CONTRIBUTING.md) pour plus de détails.