; Drush Make (http://drupal.org/project/drush_make)
; drush make -y --no-core --contrib-destination=. sharerich.make

api = 2
core = 7.x

; Libraries
libraries[rrssb][download][type] = git
libraries[rrssb][download][url] = https://github.com/kni-labs/rrssb.git
libraries[rrssb][download][revision] = 4b1e56632fe7710302911076cd7394dab88ab58a
libraries[rrssb][subdir] = .

projects[jquery_update][version] = 2.4
projects[jquery_update][subdir] = contrib


