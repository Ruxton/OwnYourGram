<?php
if($this->user->send_category_as_array != 1):
?>
  <div class="alert alert-warning" id="array_notice">
    <b>Upcoming change!</b>
    The Micropub spec now requires values be sent as arrays instead of as comma-separated lists. When
    you are ready to receive the category property as an array, click the button below to switch. <a href="https://www.w3.org/TR/micropub/#form-encoded-and-multipart-requests">More Info</a>
    <p><button class="btn btn-default" id="micropub_array">Upgrade me!</button></p>
  </div>
<?php
endif;
?>

<?php if($this->user->instagram_username && $this->user->micropub_success): ?>
  <div class="bs-callout bs-callout-success">
    <p><b>Your account is active and we're sending your Instagram photos to your site!</b></p>
    <p>Please note that due to changes in Instagram's API, we are unable to send your photos in realtime, so you may experience some delay between posting photos on Instagram and seeing them on your website.</p>
  </div>

  <h3>Settings</h3>

  <div class="panel">
    <h4>Instagram</h4>

    <p>You are logged in as <b><?= $this->user->url ?></b> and are connected to the Instagram username <b><a href="https://instagram.com/<?= $this->user->instagram_username ?>"><?= $this->user->instagram_username ?></a></b>.</p>

    <a href="javascript:disconnect_instagram()" class="btn btn-xs btn-warning">Disconnect Instagram</a>
  </div>

  <div id="automatic-syndication" class="hidden panel">
    <h4>Automatic Syndication</h4>

    <p>You can add rules that will match words in your Instagram post and tell your Micropub endpoint to syndicate your post to various destinations. You can use this to make rules like "syndicate my photos tagged #indieweb to Twitter". 

    <table class="table">
      <tr>
        <td width="110"></td>
        <td>Keyword</td>
        <td>Syndication Target</td>
      </tr>
      <?php foreach($this->rules as $rule): ?>
        <tr class="rule">
          <td align="right"><a href="#" class="hidden delete" data-id="<?= $rule->id ?>">&times;</a></td>
          <td><?= htmlspecialchars($rule->match); ?></td>
          <td><?= htmlspecialchars($rule->syndicate_to_name); ?></td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td><input type="button" id="new-syndicate-to-btn" class="btn btn-success" value="Add Rule"></td>
        <td><input type="text" id="new-syndicate-to-keyword" class="form-control"></td>
        <td>
          <select name="new-syndicate-to" id="new-syndicate-to" class="form-control">
            <option value="">-- select an endpoint --</option>
          </select>
        </td>
      </tr>
    </table>

    <p style="font-size:0.9em; color: #999;">Note that OwnYourGram won't actually post anything to Twitter or Facebook, all this does is set the appropriate parameter in the Micropub request to indicate to your Micropub endpoint that the post should be syndicated. If you don't yet have this set up, you might want to try <a href="https://silo.pub">silo.pub</a> for an easy API for posting to Twitter, Facebook and others.</p>
  </div>

  <div class="panel">
    <h4>Syndication Endpoints</h4>

    <a href="javascript:reload_syndication_endpoints()" class="btn btn-xs btn-default">Reload</a>

    <div id="syndication-endpoints">
      <div class="alert alert-warning hidden" style="margin-top: 1em;">
        <b>No Syndication Targets</b>
        <p>OwnYourGram didn't find any syndication targets at your Micropub endpoint. Learn more about <a href="https://www.w3.org/TR/micropub/#syndication-targets">Micropub syndication</a>.</p>
        <p class="error hidden">Error: <span class="details"></span></p>
      </div>
      <ul class="list" style="margin-top: 1em;"></ul>
    </div>
  </div>

  <script>
  $(function(){
    $.get("/settings/syndication-targets.json", function(data){
      handle_discovered_syndication_targets(data);
    });

    $("#new-syndicate-to-btn").click(function(){
      if($("#new-syndicate-to-keyword").val() != "" && $("#new-syndicate-to").val() != "") {
        $.post("/settings/syndication-rules.json", {
          action: 'create',
          keyword: $("#new-syndicate-to-keyword").val(),
          target: $("#new-syndicate-to").val(),
          target_name: $("#new-syndicate-to :selected").text()
        }, function(data){
          window.location = window.location; // the lazy way
        });
      }
      return false;
    });

    $("#automatic-syndication .rule").on("mouseover", function(){
      $(this).find(".delete").removeClass("hidden");
    });
    $("#automatic-syndication .rule").on("mouseout", function(){
      $(this).find(".delete").addClass("hidden");
    });
    $("#automatic-syndication .delete").click(function(){
        $.post("/settings/syndication-rules.json", {
          action: 'delete',
          id: $(this).data('id')
        }, function(data){
          window.location = window.location; // the lazy way
        });
      return false;
    });
  });

  function handle_discovered_syndication_targets(data) {
    if(data.targets) {

      $("#syndication-endpoints .list").html('');
      $("#new-syndicate-to").html('');
      $("#new-syndicate-to").append('<option value="">-- select an endpoint --</option>');
      for(var i in data.targets) {
        $("#syndication-endpoints .list").append('<li>'+data.targets[i].name+'</li>');
        $("#new-syndicate-to").append('<option value="'+data.targets[i].uid+'">'+data.targets[i].name+'</option>');
      }

      $("#syndication-endpoints .alert-warning").addClass("hidden");
      $("#automatic-syndication").removeClass("hidden");
    } else {
      if(data.error) {
        $("#syndication-endpoints .details").text(data.error);
        $("#syndication-endpoints .error").removeClass("hidden");
      }
      $("#syndication-endpoints .alert-warning").removeClass("hidden");
      $("#automatic-syndication").addClass("hidden");
    }
  }

  function reload_syndication_endpoints() {
    $("#syndication-endpoints .list").html('');
    $.post("/settings/syndication-targets.json", function(data){
      handle_discovered_syndication_targets(data);
    });
  }

  function disconnect_instagram() {
    $.post("/settings/instagram.json", {
      action: 'disconnect'
    }, function(data){
      window.location = window.location;
    })
    return false;
  }
  </script>

<?php elseif($this->user->micropub_success): ?>
  <? /* they've already verified their micropub endpoint, but have been disconnected from instagram */ ?>

  <div class="bs-callout bs-callout-warning">
    <p>There is no Instagram account associated with your account.</p>
  </div>

  <a href="/instagram" class="btn btn-success">Connect Instagram</a>

<?php else: ?>
  <div class="bs-callout bs-callout-warning">
    <p>Alright, that's progress! We're almost ready to start sending your Instagram photos to your website.</p>
  </div>

  <h2>Test Post</h2>

  <p>Before we enable automatic posting, you need to first test that your website accepts the requests that OwnYourGram sends. Some of your latest Instagram photos are below. Click the "post" button on any of them, and we'll send that photo to your website. If your website accepts the photo, then automatic posting will be enabled!</p>

  <p><a href="/docs">Read the documentation</a> on what fields OwnYourGram sends to your website.</p>

  <div id="instagram_photos"></div>
  <div style="clear:both;"></div>

  <div id="loading" class="hidden"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span></div>

  <div id="test-error" class="hidden">
    <div class="alert alert-warning">
      <b>Error!</b>
      <p>There was an error posting the photo to your website. Please check that you're handling the <a href="/docs">expected parameters</a> and returning the "Location" header and try again.</p>
    </div>
    <p>Raw response:</p>
    <pre></pre>
  </div>

  <div id="test-success" class="hidden">
    <div class="bs-callout bs-callout-success">
      <b>Great!</b>
      <p>Your website accepted the photo! View your post at the link below. Automatic import is now enabled for your site!</p>
      <a href="">View Post</a>
    </div>
  </div>

  <script>
  $(function(){
    $.get("/instagram/photos.json", function(data){
      var template = $("#photo-template").html();
      for(var i in data.items) {
        var item = $(template).clone();
        $(item).find("img").attr("src",data.items[i].instagram_img);
        $(item).find(".btn").attr("data-id", data.items[i].id);
        $("#instagram_photos").append(item);
      }
      $("#instagram_photos .btn").click(function(){
        $("#instagram_photos .btn").addClass("disabled");
        $("#loading").removeClass("hidden");

        $.post("/instagram/test.json", {
          id: $(this).data("id")
        }, function(data){
          $("#instagram_photos .btn").removeClass("disabled");
          $("#loading").addClass("hidden");

          if(data.location) {
            $("#test-error").addClass("hidden");
            $("#test-success a").attr("href", data.location);
            $("#test-success").removeClass("hidden");
          } else {
            $("#test-success").addClass("hidden");
            $("#test-error").removeClass("hidden");
            $("#test-error pre").text(data.response);
          }
        });
        return false;
      });
    });

    $("#micropub_array").click(function(){
      $.post("/prefs/array", {
        upgrade: "yes"
      }, function(data) {
        $("#array_notice").hide();
        window.location = window.location;
      });
    });

  });
  </script>
  <script id="photo-template" type="text/x-ownyourgram-template">
    <div class="col-xs-6 col-md-3">
      <div class="thumbnail">
        <img src="">
        <div class="caption" style="text-align: center;">
          <a href="" class="btn btn-success">Post</a>
        </div>
      </div>
    </div>
  </script>
<?php endif; ?>
