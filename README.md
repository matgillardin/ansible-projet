# Rôle Ansible : webdb_infrastructure

## Table des matières

1. [Présentation](#présentation)
2. [Fonctionnalités](#fonctionnalités)
3. [Technologies utilisées](#technologies-utilisées)
4. [Structure du rôle](#structure-du-rôle)
5. [Variables](#variables)
6. [Tags disponibles](#tags-disponibles)
7. [Utilisation et tests](#utilisation-et-tests)

---

## Présentation

Le rôle **webdb_infrastructure** permet de déployer automatiquement une infrastructure complète composée de :

- Un **serveur web Apache** avec PHP pour héberger une application web
- Un **serveur de base de données MariaDB** conteneurisé avec Docker
- Une **application PHP** de démonstration connectée à la base de données

Ce rôle est conçu pour fonctionner sur **Debian/Ubuntu** et **Fedora/RedHat/CentOS**, avec une détection automatique du système d'exploitation.

---

## Fonctionnalités

### Détection automatique de l'OS

Le rôle détecte automatiquement la distribution Linux et adapte :
- Le gestionnaire de paquets (`apt` ou `dnf`)
- Les noms des paquets à installer
- Les chemins de configuration
- Les noms des services

**Distributions supportées :**
- Debian (Bullseye, Bookworm)
- Ubuntu
- Fedora (38, 39, 40)
- RedHat / CentOS

### Installation de Docker

- Installation des paquets Docker selon l'OS
- Activation et démarrage du service Docker
- Ajout de l'utilisateur au groupe `docker`

### Déploiement de la base de données

- Téléchargement de l'image MariaDB depuis Docker Hub
- Création et configuration du conteneur MariaDB
- Configuration du réseau (mode host)
- Persistance des données via volume Docker
- Ouverture automatique du firewall (UFW ou firewalld)
- Exécution d'un script SQL d'initialisation

### Installation du serveur web

- Installation d'Apache avec PHP et le module MySQL
- Configuration SELinux pour Fedora (httpd_can_network_connect)
- Activation et démarrage du service Apache

### Déploiement de l'application web

- Création du répertoire DocumentRoot
- Déploiement d'une application PHP templétisée
- Configuration du VirtualHost Apache
- Activation du site (Debian uniquement)

---

## Technologies utilisées

### Infrastructure

| Composant | Technologie | Version |
|-----------|-------------|---------|
| Automatisation | Ansible | ≥ 2.1 |
| Conteneurisation | Docker | Latest |
| Base de données | MariaDB | Latest |
| Serveur web | Apache | 2.x |
| Langage backend | PHP | 7.x / 8.x |
| Firewall (Debian) | UFW | - |
| Firewall (Fedora) | firewalld | - |

### Modules Ansible utilisés

| Module | Utilisation |
|--------|-------------|
| `apt` | Gestion des paquets sur Debian/Ubuntu |
| `dnf` | Gestion des paquets sur Fedora/RedHat |
| `package` | Gestion générique des paquets (multi-OS) |
| `service` | Gestion des services système (start, enable) |
| `command` | Exécution de commandes Docker |
| `shell` | Exécution de commandes shell complexes |
| `copy` | Copie de fichiers vers les hôtes distants |
| `file` | Gestion des fichiers et répertoires |
| `template` | Déploiement de fichiers Jinja2 templétisés |
| `user` | Gestion des utilisateurs (ajout au groupe docker) |
| `ufw` | Configuration du firewall UFW (Debian) |
| `firewalld` | Configuration du firewall firewalld (Fedora) |
| `seboolean` | Gestion des booléens SELinux |
| `set_fact` | Définition dynamique de variables |
| `debug` | Affichage d'informations de debug |
| `fail` | Arrêt conditionnel avec message d'erreur |
| `include_tasks` | Inclusion dynamique de fichiers de tâches |

### Fonctionnalités Ansible utilisées

| Fonctionnalité | Description |
|----------------|-------------|
| **Handlers** | Redémarrage conditionnel d'Apache via `notify` |
| **Templates Jinja2** | Génération dynamique de `index.php` et `vhost.conf` |
| **Conditionals (`when`)** | Exécution conditionnelle selon l'OS ou le rôle serveur |
| **Loops (`until/retries`)** | Attente de la disponibilité de MariaDB |
| **Blocks** | Groupement logique de tâches (script SQL) |
| **Tags** | Exécution sélective de parties du rôle |
| **Facts** | Utilisation de `ansible_facts` pour la détection OS |
| **Ansible Vault** | Chiffrement des mots de passe sensibles |
| **Group Variables** | Variables partagées via `group_vars/all/` |

---

## Structure du rôle

```
roles/webdb_infrastructure/
├── defaults/
│   └── main.yml
├── files/
│   └── init_db.sql
├── handlers/
│   └── main.yml
├── meta/
│   └── main.yml
├── tasks/
│   ├── main.yml
│   ├── detect_os.yml
│   ├── install_docker.yml
│   ├── deploy_database.yml
│   ├── install_webserver.yml
│   └── deploy_webapp.yml
├── templates/
│   ├── index.php
│   └── vhost.conf
└── vars/
    └── main.yml
```

### 📁 Dossier `tasks/`

| Fichier | Description |
|---------|-------------|
| `main.yml` | Point d'entrée principal du rôle. Orchestre l'inclusion des autres fichiers de tâches selon le `server_role` défini (`webserver` ou `database`). Définit les tags pour chaque bloc de tâches. |
| `detect_os.yml` | Détecte la distribution Linux via `ansible_facts['distribution']`. Définit dynamiquement les variables spécifiques à l'OS : noms des paquets, chemins de configuration, gestionnaire de paquets. Échoue si l'OS n'est pas supporté. |
| `install_docker.yml` | Met à jour le cache des paquets, installe Docker et ses dépendances Python, démarre et active le service Docker, ajoute l'utilisateur courant au groupe `docker`. |
| `deploy_database.yml` | Télécharge l'image MariaDB, crée et démarre le conteneur avec les variables d'environnement (credentials, base de données), configure le firewall, copie et exécute le script SQL d'initialisation. |
| `install_webserver.yml` | Met à jour le cache des paquets, installe Apache avec PHP et le module mysqli, démarre et active le service Apache, configure SELinux sur Fedora. |
| `deploy_webapp.yml` | Crée le répertoire DocumentRoot, déploie l'application PHP via template, configure le VirtualHost Apache, active le site sur Debian avec `a2ensite`. |

### 📁 Dossier `handlers/`

| Fichier | Description |
|---------|-------------|
| `main.yml` | Contient le handler `Restart Apache` qui redémarre le service Apache. Déclenché par `notify` lors du déploiement de l'application ou de la configuration du VirtualHost. |

### 📁 Dossier `templates/`

| Fichier | Description |
|---------|-------------|
| `index.php` | Application PHP de démonstration. Affiche les informations de connexion à la base de données, liste les produits et informations système, affiche les détails du serveur PHP. Utilise les variables Ansible pour la configuration de connexion. |
| `vhost.conf` | Template de configuration Apache VirtualHost. Définit le ServerName, DocumentRoot, les permissions du répertoire et les chemins des logs. Adapté dynamiquement selon les variables définies. |

### 📁 Dossier `files/`

| Fichier | Description |
|---------|-------------|
| `init_db.sql` | Script SQL d'initialisation de la base de données. Crée les tables `produits` et `system_info`, insère des données de démonstration (5 produits), configure les privilèges utilisateur. |

### 📁 Dossier `defaults/`

| Fichier | Description |
|---------|-------------|
| `main.yml` | Variables par défaut du rôle. Contient les valeurs de configuration pour Apache, Docker et MariaDB. Ces valeurs peuvent être surchargées par les variables d'inventaire ou de groupe. |
---

## Variables

### Variables par défaut (`defaults/main.yml`)

| Variable | Description | Valeur par défaut |
|----------|-------------|-------------------|
| `project_name` | Nom du projet | `webdb_project` |
| `apache_http_port` | Port d'écoute HTTP d'Apache | `80` |
| `apache_vhost_name` | Nom de domaine du VirtualHost | `test1.com` |
| `apache_document_root` | Chemin racine des fichiers web | `/var/www/{{ apache_vhost_name }}` |
| `docker_network_name` | Nom du réseau Docker | `webdb_network` |
| `mariadb_container_name` | Nom du conteneur MariaDB | `mariadb_container` |
| `mariadb_image` | Image Docker à utiliser | `mariadb:latest` |
| `mariadb_port` | Port d'écoute MariaDB | `3306` |
| `mariadb_database` | Nom de la base de données | `webdb` |
| `mariadb_user` | Utilisateur de la base de données | `webuser` |
| `mariadb_password` | Mot de passe utilisateur | ⚠️ À définir via vault |
| `mariadb_root_password` | Mot de passe root MariaDB | ⚠️ À définir via vault |
| `app_user` | Utilisateur propriétaire des fichiers web | `www-data` |

### Variables d'inventaire

| Variable | Description | Exemple |
|----------|-------------|---------|
| `server_role` | Rôle du serveur, détermine les tâches exécutées | `webserver` ou `database` |
| `db_host` | Adresse IP du serveur de base de données | `192.168.0.12` |
| `ansible_host` | Adresse IP de l'hôte cible | `192.168.0.11` |

### Variables définies dynamiquement (`detect_os.yml`)

Ces variables sont automatiquement définies selon l'OS détecté :

| Variable | Debian/Ubuntu | Fedora/RedHat |
|----------|---------------|---------------|
| `package_manager` | `apt` | `dnf` |
| `packages_docker` | `docker.io`, `python3-docker` | `docker`, `python3-docker` |
| `packages_apache` | `apache2`, `libapache2-mod-php`, `php`, `php-mysqli` | `httpd`, `php`, `php-mysqlnd` |
| `apache_service` | `apache2` | `httpd` |
| `apache_config_path` | `/etc/apache2/sites-available` | `/etc/httpd/conf.d` |
| `app_user` | `www-data` | `apache` |
| `apache_log_dir` | `${APACHE_LOG_DIR}` | `/var/log/httpd` |

### Variables Vault (secrets chiffrés)

| Variable | Description |
|----------|-------------|
| `vault_ansible_become_pass` | Mot de passe sudo pour l'élévation de privilèges |
| `vault_mariadb_password` | Mot de passe de l'utilisateur MariaDB |
| `vault_mariadb_root_password` | Mot de passe root MariaDB |

---

## Tags disponibles

Les tags permettent d'exécuter des parties spécifiques du rôle :

| Tag | Description | Serveur cible |
|-----|-------------|---------------|
| `always` | Détection de l'OS (toujours exécuté) | Tous |
| `docker` | Installation de Docker uniquement | database |
| `database` | Déploiement complet de la base de données (inclut docker) | database |
| `webserver` | Installation du serveur web Apache | webserver |
| `webapp` | Déploiement de l'application web PHP | webserver |

---

## Utilisation et tests

### Configuration du Vault

Avant d'exécuter le playbook, vous devez configurer le fichier vault contenant les secrets :

```bash
# Créer un nouveau fichier vault
ansible-vault create inventories/group_vars/all/vault.yml

# Éditer un fichier vault existant
ansible-vault edit inventories/group_vars/all/vault.yml

# Changer le mot de passe du vault
ansible-vault rekey inventories/group_vars/all/vault.yml
```

Contenu du fichier `vault.yml` :
```yaml
---
vault_ansible_become_pass: "votre_mot_de_passe_sudo"
vault_mariadb_password: "mot_de_passe_securise"
vault_mariadb_root_password: "mot_de_passe_root_securise"
```

### Exécution du playbook

```bash
# Déploiement complet
ansible-playbook playbook.yml

# Inverser les rôles (Fedora=Web, Debian=DB)
ansible-playbook playbook.yml -e "web_server_ip=192.168.0.12" -e "db_server_ip=192.168.0.11"
```

### Utilisation des tags

```bash
# Installer uniquement Docker
ansible-playbook playbook.yml --tags "docker"

# Uniquement la base de données
ansible-playbook playbook.yml --tags database

# Déployer uniquement l'application web
ansible-playbook playbook.yml --tags "webapp"

# Uniquement le serveur web
ansible-playbook playbook.yml --tags webserver,webapp

# Tout exécuter SAUF la base de données
ansible-playbook playbook.yml --skip-tags "database"

# Tout exécuter SAUF Docker
ansible-playbook playbook.yml --skip-tags "docker"
```

### Limiter l'exécution avec `--limit`

```bash
# Exécuter uniquement sur le serveur web
ansible-playbook playbook.yml --limit web_server

# Exécuter uniquement sur le serveur de base de données
ansible-playbook playbook.yml --limit db_server

# Exécuter sur un groupe d'hôtes
ansible-playbook playbook.yml --limit webservers
```
---

## Auteur

**Mathieu Gillardin** - Henallux - Projet TI331

---
## 📚 Ressources et Documentation

### Documentation Ansible Officielle

#### Général
- [Documentation Ansible](https://docs.ansible.com/) - Documentation officielle complète

#### Modules utilisés dans ce projet
- [ansible.builtin.apt](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/apt_module.html) - Gestion des paquets Debian/Ubuntu
- [ansible.builtin.dnf](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/dnf_module.html) - Gestion des paquets Fedora/RedHat
- [ansible.builtin.package](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/package_module.html) - Gestion générique des paquets
- [ansible.builtin.service](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/service_module.html) - Gestion des services
- [ansible.builtin.command](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/command_module.html) - Exécution de commandes
- [ansible.builtin.shell](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/shell_module.html) - Exécution de commandes shell
- [ansible.builtin.copy](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/copy_module.html) - Copie de fichiers
- [ansible.builtin.file](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/file_module.html) - Gestion des fichiers et répertoires
- [ansible.builtin.template](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/template_module.html) - Déploiement de templates Jinja2
- [ansible.builtin.user](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/user_module.html) - Gestion des utilisateurs
- [ansible.builtin.set_fact](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/set_fact_module.html) - Définition de variables
- [ansible.builtin.debug](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/debug_module.html) - Affichage de debug
- [ansible.builtin.fail](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/fail_module.html) - Arrêt avec erreur
- [ansible.builtin.include_tasks](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/include_tasks_module.html) - Inclusion de tâches
- [community.general.ufw](https://docs.ansible.com/ansible/latest/collections/community/general/ufw_module.html) - Gestion du firewall UFW
- [ansible.posix.firewalld](https://docs.ansible.com/ansible/latest/collections/ansible/posix/firewalld_module.html) - Gestion du firewall firewalld
- [ansible.posix.seboolean](https://docs.ansible.com/ansible/latest/collections/ansible/posix/seboolean_module.html) - Gestion des booléens SELinux

#### Fonctionnalités Ansible
- [Playbooks](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_intro.html) - Introduction aux playbooks
- [Rôles](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_reuse_roles.html) - Structure et utilisation des rôles
- [Handlers](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_handlers.html) - Gestion des handlers
- [Variables](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_variables.html) - Gestion des variables
- [Facts](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_vars_facts.html) - Utilisation des facts
- [Conditionals (when)](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_conditionals.html) - Conditions d'exécution
- [Loops](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_loops.html) - Boucles et itérations
- [Blocks](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_blocks.html) - Groupement de tâches
- [Tags](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_tags.html) - Utilisation des tags
- [Ansible Vault](https://docs.ansible.com/ansible/latest/vault_guide/index.html) - Chiffrement des secrets
- [Inventaires](https://docs.ansible.com/ansible/latest/inventory_guide/index.html) - Gestion des inventaires
- [Group Variables](https://docs.ansible.com/ansible/latest/inventory_guide/intro_inventory.html#organizing-host-and-group-variables) - Variables de groupe
- [Privilege Escalation (become)](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_privilege_escalation.html) - Élévation de privilèges

#### Templates Jinja2
- [Templates Jinja2 dans Ansible](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_templating.html)
- [Documentation Jinja2 officielle](https://jinja.palletsprojects.com/en/3.1.x/)
- [Filtres Jinja2 dans Ansible](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_filters.html)

#### Ligne de commande
- [ansible-playbook](https://docs.ansible.com/ansible/latest/cli/ansible-playbook.html) - Commande ansible-playbook
- [ansible-vault](https://docs.ansible.com/ansible/latest/cli/ansible-vault.html) - Commande ansible-vault
- [ansible](https://docs.ansible.com/ansible/latest/cli/ansible.html) - Commande ansible (ad-hoc)

---

### Documentation Docker Officielle

- [Documentation Docker](https://docs.docker.com/) - Documentation officielle
- [Installation Docker sur Debian](https://docs.docker.com/engine/install/debian/)
- [Installation Docker sur Fedora](https://docs.docker.com/engine/install/fedora/)
- [Docker CLI Reference](https://docs.docker.com/engine/reference/commandline/docker/)
- [docker run](https://docs.docker.com/engine/reference/commandline/run/) - Création de conteneurs
- [docker pull](https://docs.docker.com/engine/reference/commandline/pull/) - Téléchargement d'images
- [docker ps](https://docs.docker.com/engine/reference/commandline/ps/) - Liste des conteneurs
- [docker exec](https://docs.docker.com/engine/reference/commandline/exec/) - Exécution dans un conteneur
- [docker start](https://docs.docker.com/engine/reference/commandline/start/) - Démarrage de conteneurs
- [Volumes Docker](https://docs.docker.com/storage/volumes/) - Persistance des données
- [Réseau Docker](https://docs.docker.com/network/) - Configuration réseau
- [Mode host network](https://docs.docker.com/network/host/) - Réseau en mode host

---

### Documentation MariaDB Officielle

- [Documentation MariaDB](https://mariadb.com/kb/en/) - Knowledge Base officielle
- [Image Docker MariaDB](https://hub.docker.com/_/mariadb) - Docker Hub officiel
- [Variables d'environnement MariaDB Docker](https://mariadb.com/kb/en/mariadb-server-docker-official-image-environment-variables/)
- [GRANT](https://mariadb.com/kb/en/grant/)
- [Gestion des utilisateurs](https://mariadb.com/kb/en/account-management-sql-commands/)

---

### Documentation Apache Officielle

- [Documentation Apache HTTP Server](https://httpd.apache.org/docs/2.4/)
- [Configuration des VirtualHosts](https://httpd.apache.org/docs/2.4/vhosts/)
- [Directive VirtualHost](https://httpd.apache.org/docs/2.4/mod/core.html#virtualhost)
- [Directive DocumentRoot](https://httpd.apache.org/docs/2.4/mod/core.html#documentroot)
- [Directive ServerName](https://httpd.apache.org/docs/2.4/mod/core.html#servername)
- [Directive Directory](https://httpd.apache.org/docs/2.4/mod/core.html#directory)
- [Directive AllowOverride](https://httpd.apache.org/docs/2.4/mod/core.html#allowoverride)
- [Directive Require](https://httpd.apache.org/docs/2.4/mod/mod_authz_core.html#require)
- [Logs Apache](https://httpd.apache.org/docs/2.4/logs.html)
- [mod_php](https://cwiki.apache.org/confluence/display/HTTPD/PHP)
- [a2ensite (Debian)](https://manpages.debian.org/bullseye/apache2/a2ensite.8.en.html)

---

### Documentation PHP Officielle

- [Documentation PHP](https://www.php.net/docs.php) - Documentation officielle
- [Extension MySQLi](https://www.php.net/manual/en/book.mysqli.php) - Connexion MySQL/MariaDB
- [mysqli::__construct](https://www.php.net/manual/en/mysqli.construct.php) - Connexion à la base
- [mysqli::query](https://www.php.net/manual/en/mysqli.query.php) - Exécution de requêtes
- [mysqli_result::fetch_assoc](https://www.php.net/manual/en/mysqli-result.fetch-assoc.php) - Récupération des résultats
- [htmlspecialchars](https://www.php.net/manual/en/function.htmlspecialchars.php) - Échappement HTML
- [try-catch](https://www.php.net/manual/en/language.exceptions.php) - Gestion des exceptions

---

### Documentation Firewall

#### UFW (Debian/Ubuntu)
- [Documentation UFW Ubuntu](https://help.ubuntu.com/community/UFW)
- [Manuel UFW](https://manpages.ubuntu.com/manpages/jammy/en/man8/ufw.8.html)
- [Guide UFW DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-22-04)

#### firewalld (Fedora/RedHat)
- [Documentation firewalld](https://firewalld.org/documentation/)
- [Guide firewalld RedHat](https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/8/html/configuring_and_managing_networking/using-and-configuring-firewalld_configuring-and-managing-networking)
- [firewall-cmd](https://firewalld.org/documentation/man-pages/firewall-cmd.html)

---

### Documentation SELinux

- [Documentation SELinux RedHat](https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/8/html/using_selinux/index)
- [SELinux et Apache](https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/8/html/using_selinux/configuring-selinux-for-applications-and-services-with-non-standard-configurations_using-selinux)
- [httpd_can_network_connect](https://linux.die.net/man/8/httpd_selinux)
- [setsebool](https://man7.org/linux/man-pages/man8/setsebool.8.html)

---

### Documentation Systèmes d'exploitation


---

### Tuto(Non-officiels)

#### Ansible
- [Ansible for DevOps (Jeff Geerling)](https://www.ansiblefordevops.com/) - Livre de référence
- [Blog Jeff Geerling](https://www.jeffgeerling.com/blog) - Tutoriels Ansible
- [DigitalOcean - Configuration Management with Ansible](https://www.digitalocean.com/community/tutorial_series/how-to-manage-configuration-with-ansible)
- [LearnLinuxTV - Ansible Tutorial](https://www.learnlinux.tv/tag/ansible/)
- [TechWorld with Nana - Ansible Tutorial](https://www.youtube.com/watch?v=1id6ERvfozo)

### Outils et Références

- [YAML Syntax](https://yaml.org/spec/1.2.2/) - Spécification YAML
- [Ansible Galaxy](https://galaxy.ansible.com/) - Partage de rôles Ansible
- [ShellCheck](https://www.shellcheck.net/) - Validateur de scripts shell
