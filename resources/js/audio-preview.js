function play(preview)
{
    let icon = preview.querySelector('.button-overlay i');
    let audio = preview.querySelector('audio');

    icon.classList.remove('bi-play-fill');
    icon.classList.add('bi-pause-fill');
    preview.dataset.playing = 'true';
    audio.volume = 0.5;
    audio.play();
}

function pause(preview)
{
    let icon = preview.querySelector('.button-overlay i');
    let audio = preview.querySelector('audio');

    icon.classList.add('bi-play-fill');
    icon.classList.remove('bi-pause-fill');
    preview.dataset.playing = 'false';
    audio.pause();
}

function pauseAll()
{
    let previews = document.querySelectorAll('.audio-preview');

    previews.forEach((preview) => {
        pause(preview);
    });
}

function onPreviewClick(event) {
    let preview = event.currentTarget;

    if (preview.dataset.playing === 'true') {
        pause(preview);
        return;
    }

    pauseAll();
    play(preview);
}

function onAudioEnded(event) {
    let preview = event.currentTarget.parentNode;

    pause(preview);
}

function initializeAudioPreviews() {
    let previews = document.querySelectorAll('.audio-preview');

    previews.forEach((preview) => {
        preview.addEventListener('click', onPreviewClick);
    });

    let audios = document.querySelectorAll('.audio-preview audio');

    audios.forEach((audio) => {
        audio.addEventListener('ended', onAudioEnded);
    });
}

document.addEventListener('DOMContentLoaded', initializeAudioPreviews);
