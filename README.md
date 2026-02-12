## Phinx Setup

### About

This branch contains setup and sample files for getting started with the database migration tool Phinx.

### Setup

Docs: [https://book.cakephp.org/phinx/0/en/](https://book.cakephp.org/phinx/0/en/)

_In your own project_, while your docker container is running, in a separate terminal, run:

Install Phinx: `docker compose run --rm php composer require robmorgan/phinx`

Init Phinx: `docker compose run --rm php vendor/bin/phinx init`

Update config details in ./phinx.php, (you can copy/paste the one from this branch)

Note you may need to create the migration directory manually: `app\db\migrations`

Referencing Phinx docs create a migration (change "CreateUserTable" to a name that makes sense for your migration): `docker compose run --rm php vendor/bin/phinx create CreateUserTable`

Run migration(s): `docker compose run --rm php vendor/bin/phinx migrate`
