#include <stdio.h>

int migratoryBirds(int arr_count, int* arr) {
    int count[6] = {0};

    // Har bird type ki frequency count karo
    for (int i = 0; i < arr_count; i++) {
        count[arr[i]]++;
    }

    int max = 0;
    int answer = 0;

    // Sabse zyada frequency wala smallest type
    for (int i = 1; i <= 5; i++) {
        if (count[i] > max) {
            max = count[i];
            answer = i;
        }
    }

    return answer;
}

int main() {
    int n;
    scanf("%d", &n);

    int arr[n];

    for (int i = 0; i < n; i++) {
        scanf("%d", &arr[i]);
    }

    int result = migratoryBirds(n, arr);

    printf("%d\n", result);

    return 0;
}
