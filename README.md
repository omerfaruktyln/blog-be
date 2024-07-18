Blog Site Application (BE)

docker network create sail

docker compose --build

docker compose up

docker run -d `
  --network sail `
  --name mysql `
  -e MYSQL_ROOT_PASSWORD=secret `
  -e MYSQL_DATABASE=blogsite `
  -e MYSQL_USER=bloguser `
  -e MYSQL_PASSWORD=password `
  -p 3306:3306 `
  mysql

php artisan migrate --seed



