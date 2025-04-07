#sudo rm test-results/ -rf
#sudo rm tests/visual.spec.js-snapshots/ -rf

#sudo docker compose up --build
sudo docker compose -f docker-compose.menu.yml up

exit



npm install

sudo docker compose up --build

## 削除
sudo rm test-results/ -rf
sudo rm test.spec.js-snapshots/ -rf


## テスト実行
sudo docker compose up

docker exec -it my-playwright-app bash
