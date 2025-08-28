sudo rm test-results/ -rf

#sudo docker compose up --build
clear
./integlight_backup.sh restore
sudo -E docker compose -f docker-compose.all.yml up


exit


