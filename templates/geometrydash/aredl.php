<div class="list">
    <form id="searchbar" method="GET" action="/gd/aredl">
        <input type="text" name="q" placeholder="Search for level..." value="<-QUERY->">
    </form>

    <div class="paging d-flex justify-content-center align-items-center gap-2 my-4">
    <a href="/gd/aredl?page=<-PREV_PAGE->" class="<-DISP_PAGES-> page-link <-DISP_PREV_PAGE->">‹</a>

    <div class="page-numbers">
        <-PAGE_LINKS->
    </div>

    <a href="/gd/aredl?page=<-NEXT_PAGE->" class="<-DISP_PAGES-> page-link <-DISP_NEXT_PAGE->">›</a>
    </div>

    <-LIST->
    <div class="paging d-flex justify-content-center align-items-center gap-2 my-4">
    <a href="/gd/aredl?page=<-PREV_PAGE->" class="<-DISP_PAGES-> page-link <-DISP_PREV_PAGE->">‹</a>

    <div class="page-numbers">
        <-PAGE_LINKS->
    </div>

    <a href="/gd/aredl?page=<-NEXT_PAGE->" class="<-DISP_PAGES-> page-link <-DISP_NEXT_PAGE->">›</a>
    </div>
</div>
