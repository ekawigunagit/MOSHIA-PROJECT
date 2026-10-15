
const pane = document.querySelector('#invitation-scroll');
const frame = document.querySelector('.phone-frame');
const root = document.querySelector('#invitation');
const controls = document.querySelector('.bottom-controls');
const music = document.querySelector('#invitation-music');
const musicButton = document.querySelector('#music-toggle');
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
function musicState() { if (musicButton) { musicButton.setAttribute('aria-pressed', String(!music.paused)); musicButton.setAttribute('aria-label', music.paused ? 'Putar musik' : 'Matikan musik'); musicButton.textContent = music.paused ? '♪' : 'Ⅱ'; } }
function openInvitation(play = false) {
    frame.classList.remove('awaiting-open'); controls.hidden = false;
    if (play && music) music.play().catch(musicState);
    const video = document.querySelector('#header-video');
    if (play && video && !reduced) video.play().catch(() => {});
}
function scrollToSection(id) { const node = document.getElementById(id); if(node) pane.scrollTo({top:node.offsetTop, behavior:reduced?'instant':'smooth'}); }
if (root.dataset.hasCover === '1' && !location.hash && root.dataset.feedback !== '1') frame.classList.add('awaiting-open'); else openInvitation();
document.querySelector('#open-invitation')?.addEventListener('click', () => {openInvitation(true); const first = [...root.querySelectorAll('.page-section')].find(s=>s.id!=='cover'); if(first) scrollToSection(first.id);});
document.querySelectorAll('.bottom-menu a').forEach(a=>{
    const target = document.querySelector(a.getAttribute('href'));
    if(!target) { a.remove(); return; }
    a.addEventListener('click',e=>{e.preventDefault();openInvitation();scrollToSection(target.id);});
});
const observer = new IntersectionObserver(entries=>{for(const e of entries)if(e.isIntersecting){document.querySelectorAll('.bottom-menu a').forEach(a=>{if(a.hash==='#'+e.target.id)a.setAttribute('aria-current','location');else a.removeAttribute('aria-current');});}}, {root:pane,threshold:.25});
root.querySelectorAll('.page-section').forEach(s=>observer.observe(s));
if(location.hash) requestAnimationFrame(()=>scrollToSection(location.hash.slice(1)));
if(music) { music.addEventListener('play',musicState);music.addEventListener('pause',musicState);musicButton.addEventListener('click',()=>{if(music.paused)music.play().catch(musicState);else music.pause();});musicState(); }
const countdown = document.querySelector('[data-countdown]');
if(countdown) {
    const target=new Date(countdown.dataset.countdown).getTime();
    const update=()=>{const seconds=Math.max(0,Math.floor((target-Date.now())/1000));if(!Number.isFinite(seconds))return;const values=[Math.floor(seconds/86400),Math.floor(seconds/3600)%24,Math.floor(seconds/60)%60,seconds%60];values.forEach((v,i)=>countdown.querySelector('[data-unit="'+i+'"]').textContent=String(v).padStart(2,'0'));document.querySelector('#countdown-status').textContent=seconds===0?'Hari istimewa telah tiba. Terima kasih atas doa Anda.':'';};
    update();setInterval(update,1000);
}
document.querySelectorAll('[data-gallery]').forEach(b=>b.addEventListener('click',()=>{const track=document.querySelector('.gallery-track');track?.scrollBy({left:Number(b.dataset.gallery)*track.clientWidth,behavior:reduced?'instant':'smooth'});}));
document.querySelectorAll('[data-copy]').forEach(b=>b.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(b.dataset.copy);document.querySelector('#copy-status').textContent='Nomor rekening disalin.';}catch{document.querySelector('#copy-status').textContent='Silakan pilih dan salin nomor rekening secara manual.';}}));
document.querySelector('#save-date')?.addEventListener('click', e=>{
    const b=e.currentTarget, esc=s=>s.replace(/\\/g,'\\\\').replace(/\r?\n/g,'\\n').replace(/,/g,'\\,').replace(/;/g,'\\;');
    const stamp=d=>d.toISOString().replace(/[-:]/g,'').replace(/\.\d{3}/,'');
    const now=new Date(), start=new Date(b.dataset.eventAt);if(!Number.isFinite(start.getTime()))return;
    const text=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Moshia//Wedding//ID','BEGIN:VEVENT','UID:'+crypto.randomUUID()+'@moshia','DTSTAMP:'+stamp(now),'DTSTART:'+stamp(start),'SUMMARY:'+esc(b.dataset.title),'LOCATION:'+esc(b.dataset.location),'END:VEVENT','END:VCALENDAR',''].join('\r\n');
    const url=URL.createObjectURL(new Blob([text],{type:'text/calendar;charset=utf-8'}));const a=document.createElement('a');a.href=url;a.download='moshia-wedding.ics';a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
});

if (root.dataset.feedback === '1') requestAnimationFrame(() => scrollToSection('rsvp'));
