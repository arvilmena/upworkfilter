`bin/console app:fetchProjects`
-- crawls Upwork and PeoplePerHour and checks for new projects

Set it up as cronjob:

```/usr/local/bin/php -d "disable_functions=" /home/doyounee/public_html/freelancer-filter/bin/console app:fetchProjects > /dev/null 2>&1```