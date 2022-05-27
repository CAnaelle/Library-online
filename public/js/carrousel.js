function initialisation() {
  imageSlider.forEach((img, i) => {
    img.style.left = i * 100 + '%';
  });
  imageSlider[current].classList.add('active');
  navigationDots();
}

function navigationDots() {
  for (let i = 0; i < numberImageSlider; i++) {
    let dot = document.createElement('div');
    dot.classList.add('dot');
    navDotsSlider.appendChild(dot);

    dot.addEventListener('click', () => {
      goToImg(i);
    });
  }

  navDotsSlider.children[0].classList.add('active');
}

/* NEXT */
function nextBtn() {
  if (current >= numberImageSlider - 1) {
    goToImg(0);
  } else {
    current++;
    goToImg(current);
  }
}

/* PREVIOUS */
function previousBtn() {
  if (current <= 0) {
    console.log(current, numberImageSlider);
    goToImg(numberImageSlider - 1);
  } else {
    current--;
    goToImg(current);
  }
}

/* Aller à la slide */
function goToImg(numberImageSlider) {
  containerSlider.style.transform =
    'translateX(-' + imgWidth * numberImageSlider + 'px)';
  current = numberImageSlider;
  activeClass();
}

function activeClass() {
  /* Mettre la classe active sur l'image actuelle */
  let active = document.querySelector('.item.active');
  active.classList.remove('active');
  imageSlider[current].classList.add('active');

  /* Mettre classe active sur vignette */
  let dot = document.querySelector('.dot.active');
  dot.classList.remove('active');
  navDotsSlider.children[current].classList.add('active');
}

/* Clavier */
function clickClavier(event) {
  switch (event.keyCode) {
    case 39:
      nextBtn();
      break;
    case 37:
      previousBtn();
      break;
  }
}
