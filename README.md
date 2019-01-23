# isrp-api
ISRP API Services

## Development

### Requirements

1. Docker
2. Google API Service Account Credentials for roleplay.org.il

### Setup Google API Service Account Credentials

1. Log in to your roleplay.org.il Google Account and open the Google Developer console.
2. Open the project "ISRP Web Service"
3. From the menu select API & Services -> Credentials
4. Click Create Credentials -> Service account key
5. Either select existing account "isrp-api" or create "New service account" with role Project Editor
6. Click "create"
7. Save the downloaded JSON file as `service-account.json` in the root directory.

### Setup docker

```
docker build -t isrp-api .
```

You have to repeat the build step if you change the library requirements in `composer.json`

### Setup environment file

Create a file named `.secrets` in the root directory and add to it the following:

```
GOOGLE_APPLICATION_CREDENTIALS=/app/service-account.json
DRAGON_CLUB_SHEET=<Dragon club sheet ID>
```

### Running

To run the server under Docker, you can use the provided Makefile.

```
make start
```

The Makefile will try to update the container image (e.g. for installing updated dependencies) and will also build
the image from scratch if it does not yet exist or was removed, then:

1. start the container
2. attach to the container logs and tail them
3. wait for the user to issue CTRL-C ("break")
4. stop and remove the container
