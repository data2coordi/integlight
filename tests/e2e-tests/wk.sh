#sudo rm test-results/ -rf
#sudo rm tests/visual.spec.js-snapshots/ -rf


clear
./integlight_backup.sh restore
sudo -E docker compose -f docker-compose.visual.yml up




exit




## 削除
sudo rm test-results/ -rf
sudo rm test.spec.js-snapshots/ -rf

## build

#npm install とRUN npx playwright install でインストールしたものを削除する。これが、ないとdocker-composeでボリュームを指定しているため、installが上書きされる。
#削除すると、インストールされたものでボリューム側を初期化するのがdocker-composeの仕様
sudo docker compose -f docker-compose.visual.yml down --volumes --remove-orphans
#
sudo docker compose -f docker-compose.visual.yml up --build


