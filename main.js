const textarea = document.getElementById('ids');
const filterButtons = document.querySelectorAll('#filters button');
const copyButton = document.getElementById('copyButton');

const exclusiveFilters = {
    distance: null,
    traded: null,
    shadow: null,
    shiny: null,
};

const getFilterGroup = (filterId) => {
    if (filterId.startsWith('distance')) return 'distance';
    if (['traded', '!traded'].includes(filterId)) return 'traded';
    if (['shadow', '!shadow'].includes(filterId)) return 'shadow';
    if (['shiny', '!shiny'].includes(filterId)) return 'shiny';
    return null;
};

const removeFilter = (filters, target) => filters.filter(f => f !== target);

filterButtons.forEach(button => {
    button.addEventListener('click', function () {
        const filterId = this.id;
        const currentFilters = textarea.value.trim() ? textarea.value.trim().split('&') : [];
        const filterGroup = getFilterGroup(filterId);
        let updatedFilters = [...currentFilters];

        if (filterGroup) {
            const current = exclusiveFilters[filterGroup];

            if (current === filterId) {
                updatedFilters = removeFilter(updatedFilters, filterId);
                exclusiveFilters[filterGroup] = null;
                this.classList.remove('active');
            } else {
                if (current) {
                    updatedFilters = removeFilter(updatedFilters, current);
                    const prevBtn = document.getElementById(current);
                    if (prevBtn) prevBtn.classList.remove('active');
                }

                updatedFilters.push(filterId);
                exclusiveFilters[filterGroup] = filterId;
                this.classList.add('active');
            }
        } else {
            const isActive = updatedFilters.includes(filterId);

            if (isActive) {
                updatedFilters = removeFilter(updatedFilters, filterId);
                this.classList.remove('active');
            } else {
                updatedFilters.push(filterId);
                this.classList.add('active');
            }
        }

        textarea.value = updatedFilters.join('&');
    });
});


copyButton.addEventListener('click', function () {
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    try {
        const successful = document.execCommand('copy');
        const message = successful ? 'Text copied to clipboard!' : 'Unable to copy text. Please try again.';
        alert(message);
        console.log('Copying text command was ' + (successful ? 'successful' : 'unsuccessful'));
    } catch (err) {
        console.error('Unable to copy text: ', err);
        alert('Unable to copy text. Please try again.');
    }

    textarea.blur();
});