# Base de données :

![Bdd schema](img/bdd.png)

## Explications :

### Contact

Cette table contient tous les messages envoyés par les utilisateurs sur la page contact.

### User

Cette table représente tous les utilisateurs du site (clients, restaurateurs, administrateurs).
Elle contient leurs informations personnelles.

### Restaurants

Cette table représente tous les restaurants et leurs informations personnelles.
Elle contient le restaurateur qui est unique.

Le champ is_Valid :
* Si la valeur est null => le restaurant est en attente et il doit être validé ou refusé.
* Si la valeur est à 1 => le restaurant a été validé par un admin.
* Si la valeur est à 0 => le restaurant a été refusé par un admin.

### Plats 
Contient les informations relatives aux plats.
Contient également le restaurant auquel il appartient.

### Commandes
Contient les informations relatives aux commandes des utilisateurs cela représente la liste des plats qu'ils ont commandés.
Contient de plus, une référence à l'utilisateur et au restaurant.

Nous **stockons les frais de livraisons** car si les frais de livraisons changent, ça **n'affecte pas l'historique de la commande passée.**

Le champ Status :

* Si la valeur est à 1 => la commande a été livrée.
* Si la valeur est à 0 => la commande n'a pas été livrée.

### Commandes_details :

Cette table permet de stocker tous les plats d'une commande.
Nous **stockons le prix** car si le prix d'un plat change, ça **n'affecte pas l'historique de la commande passée.**


## Explication des relations :

### Relation Membres - Restaurants :

Initialement un membre est un client.
Mais il peut néanmoins **devenir restaurateur tout en restant client.**
**Il devient restaurateur lorsqu'il crée un restaurant.**



