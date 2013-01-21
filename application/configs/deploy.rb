# general settings
default_run_options[:pty] = true
set :use_sudo, true
 
# source control settings
set :scm, :git
set :deploy_via, :remote_cache
set :repository, "git@github.com:ywarezk/nerdeez.com.git"
 
role :app, "nerdeez.com"
role :web, "nerdeez.com"
role :db, "nerdeez", :primary => true
 
set :deploy_to, "/home/ywarezk2824/public_html/"

set :user, "ywarezk2824"
set :password, "KhruiNhe$%Vhpv"

namespace :deploy do

set :branch, "production"
 
task :migrate do
# overrides the standard Rails database migrations task
end

task :start, :roles => :app do
 
end
 
task :stop, :roles => :app do
 
end
 


task :restart, :roles => :app do
# no restart required for Apache/mod_php
end
 
desc "minify js and css"
task :minify do
    print "yariv!!!!!!!!!!!" 
    run "cd #{release_path} && juicer merge -i --force ./public/js/static.js"
    run "cd #{release_path} && juicer merge -d ./public --force ./public/styles/static.css"
    run "cd #{release_path} && chmod +x ./application/models/Nerdeez_Backup_Db_To_S3"
end

end



after 'deploy:update_code', 'deploy:minify'