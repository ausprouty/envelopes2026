export function formatDate(
    value: string | null,
): string {
    if (!value) {
        return '';
    }

    const [year, month, day] =
        value.substring(0, 10).split('-');

    const date = new Date(
        Number(year),
        Number(month) - 1,
        Number(day),
    );

    return date.toLocaleDateString('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
