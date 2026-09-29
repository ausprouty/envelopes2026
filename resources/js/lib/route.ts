import type {
    Config,
    RouteName,
    RouteParams,
} from 'ziggy-js';

import { route as ziggyRoute } from 'ziggy-js';

import { Ziggy } from '@/ziggy';

export function route<T extends RouteName>(
    name: T,
    params?: RouteParams<T>,
) {
    return ziggyRoute(
        name,
        params,
        undefined,
        Ziggy as Config,
    );
}
