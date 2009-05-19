<?php
//    // Если файла не существует, или файл старше 10 минут, то удаляем старый и загружаем новый
//    if (!file_exists(SYS_DIR_CACHE."twitter.html") or filemtime(SYS_DIR_CACHE."twitter.html") > (time()+600)) {
        require_once SYS_DIR_LIBS . 'external/class.twitter.php';
        $twitter = new twitter;
        $timeline = $twitter->userTimeline(false, 3);
        foreach ($timeline->status as $status) {
            echo "<li><a target='_blank' href='http://twitter.com/home_money/status/{$status->id}'>{$status->text}</a>";
        }
        exit();
//    }