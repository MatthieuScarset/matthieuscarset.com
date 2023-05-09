/**
 * @file
 * Attaches behaviors for sliders.
 */

(function (Drupal) {
  /**
   * Enable slider.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.sliders = {
    makeMeSlide(element) {
      const slide = element.children;

      slide.addEventListener("click", () => {
        const slideWidth = slide.clientWidth;
        slidesContainer.scrollLeft += slideWidth;
      });

      /*
      prevButton.addEventListener("click", () => {
        const slideWidth = slide.clientWidth;
        slidesContainer.scrollLeft -= slideWidth;
      });
      */
    },

    attach(context) {
      const sliders = once('slider', '[data-slider]', context);
      if (!sliders.length) {
        return;
      }

      sliders.forEach(element => {
        this.makeMeSlide(element);
      });
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        once.remove('slider', context.querySelectorAll('[data-slider]'));
      }
    },
  };
})(Drupal);
