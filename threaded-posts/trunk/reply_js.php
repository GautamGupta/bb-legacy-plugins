<script type="text/javascript">

function reply_to(post_id) {
    var post_parent = document.getElementById('post_parent');
    var postform = document.getElementById('postform');

    if (!postform) {
      alert("Please log in to post!");
      return;
    }

    post_parent.value = post_id;

    ScrollTo(postform);

    /* We don't really want quoting */
    /*
    post_contents = document.getElementById('post_contents-' + post_id).innerHTML;
    post_contents = post_contents.replace(/<br>/, "\n");
    post_contents = post_contents.replace(/<p>/, '');
    post_contents = post_contents.replace(/<\/p>/, '');
    post_contents = post_contents.replace(/^/g, '> ');
    post_contents = post_contents.replace(/\n/g, "\n> ');

    post_content = document.getElementById('post_content');
    post_content.value = post_contents;
     */
}

function ScrollTo(element){

    var selectedPosX = 0;
    var selectedPosY = 0;

    while(element != null){
        selectedPosX += element.offsetLeft;
        selectedPosY += element.offsetTop;
        element = element.offsetParent;
    }

    console.log("x: " + selectedPosX + " y: " + selectedPosY);
    window.scrollTo(selectedPosX,selectedPosY);
}

</script>
