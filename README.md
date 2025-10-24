# Green Logistics - Marketplace Logistique

## Ce projet est une application web de place de marché logistique. Elle fournit une API aux Partenaires Externes pour soumettre des demandes de livraison.

### API pour les Partenaires

Ce point de terminaison est utilisé pour soumettre de nouvelles livraisons à la marketplace.

URL : http://localhost/green-logistics/api_create_delivery.php

Méthode : POST

Corps (JSON) :

```{
    "Source": "123 Rue de Départ, 75001 Paris",
    "Destination": "456 Avenue d'Arrivée, 69001 Lyon",
    "Weight": 1500,
    "isBulky": false,
    "isFresh": true
}```




### Outils Recommandés

Pour l'environnement serveur (PHP/MySQL) :

XAMPP (Serveur Apache + MySQL + PHP)

Pour tester l'API :

Bruno (Client API open-source et hors-ligne)