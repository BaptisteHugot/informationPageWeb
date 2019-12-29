# informationPageWeb
Ce programme permet d'extraire les informations suivantes concernant une URL entrée préalablement par un utilisateur :
* Les informations sur les serveurs du domaine (adresses IPv4 et IPv6), comprenant la liste des derniers sites testés, un hall of fame (sites dont tous les serveurs sont disponibles en IPv6) et un hall of shame (sites dont tous les serveurs sont disponibles en IPv4 uniquement)
* Le Whois du domaine
* Les entêtes HTTP de la page
* Les balises meta de la page
* Les adresses IP de l'utilisateur (IPv4 et IPv6)
* Le User Agent du navigateur de l'utilisateur

Ce programme contient également deux sous-dossiers :
* cronCheck, permettant d'effectuer un Cron mettant à jour la table contenant la liste des serveurs des domaines déjà testés et dont le dernier test remonte à plus d'une semaine
* fileUpload, permettant de faire des tests sur les serveurs de plusieurs domaines en même temps à l'aide d'un fichier au format .csv préalablement rempli (un exemple de tel fichier est disponible dans le sous-dossier)

## Exemple
Un exemple complet de ce programme est disponible sur [mon site personnel](https://www.baptistehugot.cf/github/informationPageWeb/index.php).

## Ecrit avec
* [PHP](https://secure.php.net/) - Le langage de programmation utilisé pour récupérer les informations pertinentes
* [HTML](https://www.w3.org/html/) - Le langage de programmation utilisé pour afficher la page Internet
* [CSS](https://www.w3.org/Style/CSS/) - Le langage de programmation utilisé pour gérer les styles de la page Internet

## Bibliothèques utilisées
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