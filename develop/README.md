# Passwordcockpit - develop environment

## General 

This README will explain the steps needed to setup and start the Passwordcockpit for development purposes.

## Requirements

[Docker](https://www.docker.com/) and [docker-compose](https://docs.docker.com/compose/) are needed for the setup.

## Installation

Go to your /etc/hosts and assign your private IP to the virtualhost password.local

Make sure the current working directory is `./develop`.

Then simply run `docker-compose up`.

Images needed by the docker-compose file are provided from [its Docker Hub organization](https://hub.docker.com/u/passwordcockpit).

This will install the Passwordcockpit webapp, and create in the current folder `backend`, `frontend` and `database` folders.
The `backend` and `frontend` folders are GIT projects.<br>
The database will already contain some tests data.

The environment variables can be modified, but please note that if you want to modify PASSWORDCOCKPIT_BLOCK_CIPHER_KEY, all passwords should be deleted from the database since they are encrypted with this key.

## First steps

the `docker-compose up` command may take a while. The web application will be ready when the following message appears:
```
passwordcockpit_frontend    | Build successful (....ms) – Serving on http://localhost:4200/
```

The current branch on both `frontend` and `backend` is `develop`, but is an old develop branch. Please check the GIT and see the current branch. You should checkout to this branch and call `docker-compose restart passwordcockpit_frontend`.

After that, go to `http://password.local:4200`. If everything went correctly, there should be a login page.<br>

The default admin login credentials are:
- username: admin
- password: Admin123!

Default user:
- username: user
- password: User123!

If the first login results in a `Undefined error`, the certificate needs to be accepted. In most browsers, go to `http://password.local:4344` and accept it.