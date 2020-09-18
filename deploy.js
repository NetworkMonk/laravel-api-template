const FtpDeploy = require('ftp-deploy');
const ftp = new FtpDeploy();
const config = require('./deploy.json');

ftp.on("uploaded", function(data) {
  console.log('[' + data.transferredFileCount + '/' + data.totalFilesCount + ']: ' + data.filename);
});
ftp.on("upload-error", function(data) {
  console.log(data.err); // data will also include filename, relativePath, and other goodies
});

ftp.deploy(config)
.then(res => console.log('Application Deployed'))
.catch(err => console.log(err));