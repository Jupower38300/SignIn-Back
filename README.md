Pour pouvoir lancer ce projet vous avez besoin d'une base de données Docker avec la commande:

```
docker compose up
```

Ensuite vous pouvez lancer la commande:

```
composer install
```

Afin d'avoir toutes les dépendances et enfin:

```
symfony server:start --allow-http --allow-all-ip --port=8000
```

Afin de lancer le serveur en local.
