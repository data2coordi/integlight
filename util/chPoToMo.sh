## delete
rm -f ../languages/*.mo
## mo生成
src=../languages/ja.po
tgt=../languages/ja.mo
msgfmt ${src} -o ${tgt}


src=../languages/tgmpa-ja.po
tgt=../languages/tgmpa-ja.mo
msgfmt ${src} -o ${tgt}

exit

