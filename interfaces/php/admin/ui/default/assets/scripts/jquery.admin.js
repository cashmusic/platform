/*
 * Default text - jQuery plugin for html5 dragging files from desktop to browser
 *
 * Author: Weixi Yen
 *
 * Email: [Firstname][Lastname]@gmail.com
 *
 * Copyright (c) 2010 Resopollution
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.github.com/weixiyen/jquery-filedrop
 *
 * Version:  0.1.0
 *
 * Features:
 *      Allows sending of extra parameters with file.
 *      Works with Firefox 3.6+
 *      Future-compliant with HTML5 spec (will work with Webkit browsers and IE9)
 * Usage:
 * 	See README at project homepage
 *
 */
;(function($) {

  jQuery.event.props.push("dataTransfer");

  var default_opts = {
      fallback_id: '',
      url: '',
      refresh: 1000,
      paramname: 'userfile',
      maxfiles: 25,           // Ignored if queuefiles is set > 0
      maxfilesize: 1,         // MB file size limit
      queuefiles: 0,          // Max files before queueing (for large volume uploads)
      queuewait: 200,         // Queue wait time if full
      data: {},
      headers: {},
      drop: empty,
      dragEnter: empty,
      dragOver: empty,
      dragLeave: empty,
      docEnter: empty,
      docOver: empty,
      docLeave: empty,
      beforeEach: empty,
      afterAll: empty,
      rename: empty,
      error: function(err, file, i) {
        alert(err);
      },
      uploadStarted: empty,
      uploadFinished: empty,
      progressUpdated: empty,
      speedUpdated: empty
      },
      errors = ["BrowserNotSupported", "TooManyFiles", "FileTooLarge"],
      doc_leave_timer, stop_loop = false,
      files_count = 0,
      files;

  $.fn.filedrop = function(options) {
    var opts = $.extend({}, default_opts, options);

    this.bind('drop', drop).bind('dragenter', dragEnter).bind('dragover', dragOver).bind('dragleave', dragLeave);
    $(document).bind('drop', docDrop).bind('dragenter', docEnter).bind('dragover', docOver).bind('dragleave', docLeave);

    $('#' + opts.fallback_id).change(function(e) {
      opts.drop(e);
      files = e.target.files;
      files_count = files.length;
      upload();
    });
    
    function drop(e) {
        opts.drop(e);
        files = e.dataTransfer.files;
        if (files === null || files === undefined) {
          opts.error(errors[0]);
          return false;
        }
        files_count = files.length;
        upload();
        e.preventDefault();
        return false;
      }

      function getBuilder(filename, filedata, mime, boundary) {
        var dashdash = '--',
            crlf = '\r\n',
            builder = '';

        if (opts.data) {
          var params = $.param(opts.data).split(/&/);

          $.each(params, function() {
            var pair = this.split(/=/, 2);
            var name = decodeURI(pair[0]);
            var val = decodeURI(pair[1]);

            builder += dashdash;
            builder += boundary;
            builder += crlf;
            builder += 'Content-Disposition: form-data; name="' + name + '"';
            builder += crlf;
            builder += crlf;
            builder += val;
            builder += crlf;
          });
        }

        builder += dashdash;
        builder += boundary;
        builder += crlf;
        builder += 'Content-Disposition: form-data; name="' + opts.paramname + '"';
        builder += '; filename="' + filename + '"';
        builder += crlf;

        builder += 'Content-Type: ' + mime;
        builder += crlf;
        builder += crlf;

        builder += filedata;
        builder += crlf;

        builder += dashdash;
        builder += boundary;
        builder += dashdash;
        builder += crlf;
        return builder;
      }

      function progress(e) {
        if (e.lengthComputable) {
          var percentage = Math.round((e.loaded * 100) / e.total);
          if (this.currentProgress != percentage) {

            this.currentProgress = percentage;
            opts.progressUpdated(this.index, this.file, this.currentProgress);

            var elapsed = new Date().getTime();
            var diffTime = elapsed - this.currentStart;
            if (diffTime >= opts.refresh) {
              var diffData = e.loaded - this.startData;
              var speed = diffData / diffTime; // KB per second
              opts.speedUpdated(this.index, this.file, speed);
              this.startData = e.loaded;
              this.currentStart = elapsed;
            }
          }
        }
      }

      // Respond to an upload
      function upload() {

        stop_loop = false;

        if (!files) {
          opts.error(errors[0]);
          return false;
        }

        var filesDone = 0,
            filesRejected = 0;

        if (files_count > opts.maxfiles && opts.queuefiles === 0) {
          opts.error(errors[1]);
          return false;
        }

        // Define queues to manage upload process
        var workQueue = [];
        var processingQueue = [];
        var doneQueue = [];

        // Add everything to the workQueue
        for (var i = 0; i < files_count; i++) {
          workQueue.push(i);
        }

        // Helper function to enable pause of processing to wait
        // for in process queue to complete
        var pause = function(timeout) {
            setTimeout(process, timeout);
            return;
        }

        // Process an upload, recursive
        var process = function() {

    	        var fileIndex;

    	        if (stop_loop) return false;

    	        // Check to see if are in queue mode
    	        if (opts.queuefiles > 0 && processingQueue.length >= opts.queuefiles) {

    	          return pause(opts.queuewait);

    	        } else {

    	          // Take first thing off work queue
    	          fileIndex = workQueue[0];
    	          workQueue.splice(0, 1);

    	          // Add to processing queue
    	          processingQueue.push(fileIndex);

    	        }

    	        try {
    	          if (beforeEach(files[fileIndex]) != false) {
    	            if (fileIndex === files_count) return;
    	            var reader = new FileReader(),
    	                max_file_size = 1048576 * opts.maxfilesize;

    	            reader.index = fileIndex;
    	            if (files[fileIndex].size > max_file_size) {
    	              opts.error(errors[2], files[fileIndex], fileIndex);
    	              // Remove from queue
    	              processingQueue.forEach(function(value, key) {
    	                if (value === fileIndex) processingQueue.splice(key, 1);
    	              });
    	              filesRejected++;
    	              return true;
    	            }
    	            reader.onloadend = send;
    	            reader.readAsBinaryString(files[fileIndex]);

    	          } else {
    	            filesRejected++;
    	          }
    	        } catch (err) {
    	          // Remove from queue
    	          processingQueue.forEach(function(value, key) {
    	            if (value === fileIndex) processingQueue.splice(key, 1);
    	          });
    	          opts.error(errors[0]);
    	          return false;
    	        }

    	        // If we still have work to do,
    	        if (workQueue.length > 0) {
    	          process();
    	        }

            };

        var send = function(e) {

          var fileIndex = ((typeof(e.srcElement) === "undefined") ? e.target : e.srcElement).index

          // Sometimes the index is not attached to the
          // event object. Find it by size. Hack for sure.
          if (e.target.index == undefined) {
            e.target.index = getIndexBySize(e.total);
          }

          var xhr = new XMLHttpRequest(),
              upload = xhr.upload,
              file = files[e.target.index],
              index = e.target.index,
              start_time = new Date().getTime(),
              boundary = '------multipartformboundary' + (new Date).getTime(),
              builder;

          newName = rename(file.name);
          mime = file.type
          if (typeof newName === "string") {
            builder = getBuilder(newName, e.target.result, mime, boundary);
          } else {
            builder = getBuilder(file.name, e.target.result, mime, boundary);
          }

          upload.index = index;
          upload.file = file;
          upload.downloadStartTime = start_time;
          upload.currentStart = start_time;
          upload.currentProgress = 0;
          upload.startData = 0;
          upload.addEventListener("progress", progress, false);

          xhr.open("POST", opts.url, true);
          xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);

          // Add headers
          $.each(opts.headers, function(k, v) {
            xhr.setRequestHeader(k, v);
          });

          xhr.sendAsBinary(builder);

          opts.uploadStarted(index, file, files_count);

          xhr.onload = function() {
            if (xhr.responseText) {
              var now = new Date().getTime(),
                  timeDiff = now - start_time,
                  result = opts.uploadFinished(index, file, jQuery.parseJSON(xhr.responseText), timeDiff);
              filesDone++;

              // Remove from processing queue
              processingQueue.forEach(function(value, key) {
                if (value === fileIndex) processingQueue.splice(key, 1);
              });

              // Add to donequeue
              doneQueue.push(fileIndex);

              if (filesDone == files_count - filesRejected) {
                afterAll();
              }
              if (result === false) stop_loop = true;
            }
          };

        }

        // Initiate the processing loop
        process();

      }

      function getIndexBySize(size) {
        for (var i = 0; i < files_count; i++) {
          if (files[i].size == size) {
            return i;
          }
        }

        return undefined;
      }

      function rename(name) {
        return opts.rename(name);
      }

      function beforeEach(file) {
        return opts.beforeEach(file);
      }

      function afterAll() {
        return opts.afterAll();
      }

      function dragEnter(e) {
        clearTimeout(doc_leave_timer);
        e.preventDefault();
        opts.dragEnter(e);
      }

      function dragOver(e) {
        clearTimeout(doc_leave_timer);
        e.preventDefault();
        opts.docOver(e);
        opts.dragOver(e);
      }

      function dragLeave(e) {
        clearTimeout(doc_leave_timer);
        opts.dragLeave(e);
        e.stopPropagation();
      }

      function docDrop(e) {
        e.preventDefault();
        opts.docLeave(e);
        return false;
      }

      function docEnter(e) {
        clearTimeout(doc_leave_timer);
        e.preventDefault();
        opts.docEnter(e);
        return false;
      }

      function docOver(e) {
        clearTimeout(doc_leave_timer);
        e.preventDefault();
        opts.docOver(e);
        return false;
      }

      function docLeave(e) {
        doc_leave_timer = setTimeout(function() {
          opts.docLeave(e);
        }, 200);
      }
  };
  function empty() {}

  try {
    if (XMLHttpRequest.prototype.sendAsBinary) return;
    XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
      function byteValue(x) {
        return x.charCodeAt(0) & 0xff;
      }
      var ords = Array.prototype.map.call(datastr, byteValue);
      var ui8a = new Uint8Array(ords);
      this.send(ui8a.buffer);
    }
  } catch (e) {}

})(jQuery);


$(document).ready(function() {

  /* begin mootools port */
  
	$('#pagetips').hide();
  
	$('#tipslink').on('click', function(e) {
		e.preventDefault();
		
		$('#pagetips').fadeIn();
	});
  
	$('#tipscloselink').on('click', function(e) {
		e.preventDefault();
		
		$('#pagetips').fadeOut();
	});
  
	$('.navitem').on('click', function(e) {
	  e.preventDefault();
    
	    window.location = $(this).find('a').attr('href');
	});
  
	$('.needsconfirmation').on('click', function(e) {
		e.preventDefault();
		
		doModalConfirm( $(this).attr('href'));
	});
	
	$('.showelementdetails').on('click', function(e) {
		e.preventDefault();

    $(this).html( function(e) {
      var t = $(this).html(),
      isShown = $(this).parents('.itemnav').prev('.elementdetails').hasClass('detailsshown');

      if ( isShown ) {
        t = t.replace(/Less/g, 'More');
        
      } else {
        t = t.replace(/More/g, 'Less');
      }

      return t;

    }).parents('.itemnav').prev('.elementdetails').toggleClass('detailsshown');
	});


  /* end mootools port */



  $('.uploadhere').filedrop({
      fallback_id: 'upload_button',    // an identifier of a standard file input element
      url: '/',              // upload handler, handles each file separately
      paramname: 'userfile',          // POST parameter name used on serverside to reference file
      data: {
          param1: 'upload-service',           // send POST variables
          param2: $(this).data('upload-service'),
      },
      headers: {          // Send additional request headers
          //'header': 'value'
      },
      error: function(err, file) {
          switch(err) {
              case 'BrowserNotSupported':
                  alert('browser does not support html5 drag and drop')
                  break;
              case 'TooManyFiles':
                  // user uploaded more than 'maxfiles'
                  break;
              case 'FileTooLarge':
                  // program encountered a file whose size is greater than 'maxfilesize'
                  // FileTooLarge also has access to the file which was too large
                  // use file.name to reference the filename of the culprit file
                  break;
              default:
                  break;
          }
      },
      maxfiles: 25,
      maxfilesize: 20,    // max file size in MBs
      dragOver: function() {
          // user dragging files over #dropzone
      },
      dragLeave: function() {
          // user dragging files out of #dropzone
      },
      docOver: function() {
          // user dragging files anywhere inside the browser document window
      },
      docLeave: function() {
          // user dragging files out of the browser document window
      },
      drop: function() {
        
        console.log( $(this) );
        
        console.log('you are uploading to: ', $(this).data('upload-service') );

      },
      uploadStarted: function(i, file, len){
          // a file began uploading
          // i = index => 0, 1, 2, 3, 4 etc
          // file is the actual file of the index
          // len = total files user dropped
          
          console.log('uploadStarted');
          //console.log('i: ',i);
          console.log('file: ',file);
          //console.log('len: ',len);
      },
      uploadFinished: function(i, file, response, time) {
          // response is the data you got back from server in JSON format.
          
          console.log('uploadFinished');
          //console.log('i: ',i);
          //console.log('file: ',file);
          console.log('response: ',response);
          //console.log('time: ',time);
      },
      progressUpdated: function(i, file, progress) {
          // this function is used for large files and updates intermittently
          // progress is the integer value of file being uploaded percentage to completion
          
          console.log('progressUpdated');
          //console.log('i: ',i);
          //console.log('file: ',file);
          console.log('progress: ',progress);
      },
      speedUpdated: function(i, file, speed) {
          // speed in kb/s
      },
      rename: function(name) {
          // name in string format
          // must return alternate name as string
      },
      beforeEach: function(file) {
          // file is a file object
          // return false to cancel upload
      },
      afterAll: function() {
          // runs after all files have been uploaded or otherwise dealt with
      }
  });


});
