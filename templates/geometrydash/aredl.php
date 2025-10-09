<div class="list">
    <form id="searchbar" method="GET" action="/gd/aredl">
        <div class="search-wrap">
            <button type="button" class="submit-aredl-search" style="margin-right: 5px;">
                <svg viewBox="0 0 24 24" aria-hidden="true" style="color: white;">
                    <use href="#checkmark"></use>
                </svg>
            </button>
            <input type="text" name="q" placeholder="Search for level..."value="<-QUERY->">
            <button type="submit" class="submit-aredl-search">
                <svg viewBox="0 0 24 24" aria-hidden="true" style="color: white;">
                    <use href="#search-mag-glass"></use>
                </svg>
            </button>
        </div>
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