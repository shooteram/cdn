#### take it for a spin
```sh
git clone git@github.com:shooteram/cdn.git
cd cdn
composer install
```

To upload a file, you need to make a POST request to the [`/api/file/add`](config/routes/app.yaml#L6) path.  
You need to pass an `x-auth-token` header to be able to do so, hence the following command.
```sh
bin/console app:create-user --username shooteram
```
_Since the generated token is the only piece of information to authenticate you, the username specified is not important._  
The previous command returns, for example, the following:
```sh
Hello shooteram, your token: 2dc634fb8b437c7417441d88b66f3fec1564a3c1188a491d3c686c959e607ca2
```

Once you have your token, you can add files.  
You need to pass a project name with at least one file while using the `multipart/form-data` `content-type`.
```sh
curl --request POST \
  --url https://localhost:8000/api/file/add \
  --header 'Accept: application/json' \
  --header 'Content-Type: multipart/form-data' \
  --header 'X-Auth-Token: 2dc634fb8b437c7417441d88b66f3fec1564a3c1188a491d3c686c959e607ca2' \
  --form project=annihilation \
  --form "js=@../lib/main.js" \
  --form "jsminimified=@../lib/main.min.js" \
  --form "image=@../lib/logo.png"
```
_Here, the files' form names, are not important but needs to be unique to be handled properly._

Your files will then be available to [the following locations](config/routes/app.yaml#L11):
  - https://localhost:8000/@annihilation/main.js
  - https://localhost:8000/@annihilation/main.min.js
  - https://localhost:8000/@annihilation/logo.png
