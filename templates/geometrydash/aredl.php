<div class="list">
    <form id="searchbar" method="POST" action="/gd/aredl">
        <div class="search-wrap">
            <button id="check_levels" type="button" class="<-COMPLETED-> submit-aredl-search" style="margin-right: 5px;">
                <svg viewBox="0 0 24 24" aria-hidden="true" style="color: white;">
                    <use href="#checkmark"></use>
                </svg>
            </button>
            <input name="checked" value="<-CHECKED->" type="hidden" id="hidden_checked">
            <input type="text" name="q" placeholder="Search for level..."value="<-QUERY->">
            <button type="submit" class="submit-aredl-search">
                <svg viewBox="0 0 24 24" aria-hidden="true" style="color: white;">
                    <use href="#search-mag-glass"></use>
                </svg>
            </button>
        </div>
    </form>
    <p class="text-center mt-n3" style="color: white;">
        <-LEVELCOUNT-> results found 
        <span style="color: rgba(255,255,255,0.3); text-shadow: 0 0 2px rgba(255,255,255,0.015);">
            (<-QUERYTIME->s.)
        </span>
    </p>
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