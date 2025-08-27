sudo rm test-results/ -rf

#sudo docker compose up --build
clear
./integlight_backup.sh restore
sudo -E docker compose -f docker-compose.all.yml up
#sudo docker compose -f docker-compose.menu.yml up
#sudo -E docker compose -f docker-compose.customiser.yml up

#sudo -E docker compose -f docker-compose.customiser.home.yml up
#sudo -E docker compose -f docker-compose.pf.image.home.yml up
#sudo -E docker compose -f docker-compose.pf.image.post.yml up

#sudo -E docker compose -f docker-compose.slider.yml up



exit



npm install

sudo docker compose up --build

## 削除
sudo rm test-results/ -rf
sudo rm test.spec.js-snapshots/ -rf


## テスト実行
sudo docker compose up

docker exec -it my-playwright-app bash
