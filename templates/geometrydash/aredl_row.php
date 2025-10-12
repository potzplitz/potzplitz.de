<a href="/gd/aredl/<-LEVELID->">
    <div class="gdAREDLContainer container <-COMPLETED->" style="background-image: url('<-THUMBNAIL->')">
        <div class="blur"></div>
        <h2 class="fs-4 fw-bold"><-LEVELNAME-></h2>
        <p class="fs-6">by <-CREATOR-></p>
        <p class="placement fs-4">#<-PLACEMENT-></p>

        <input type="button" id="checkbutton_<-COUNTER->" class="checkbutton" value="<-BUTTONTEXT->">
        <input type="number" id="attemptsfield_<-COUNTER->" data-for="checkbutton_<-COUNTER->" class="attemptsfield" placeholder="attempts" value="<-ATTEMPTS->">
        <input type="hidden" id="lvlid_<-COUNTER->" data-levelid="<-LEVELID->" data-forlvl="checkbutton_<-COUNTER->">
    </div>
</a>