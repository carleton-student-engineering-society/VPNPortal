#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>
#include <ctype.h>
#include <string.h>

int main(int argc, char **argv){
    if (argc != 3){
        printf("%i - Usage: ./gen_cert <dir> <username>\n", argc);
        return 1;
    }
    if (strcmp("easy-rsa", argv[1]) != 0 && strcmp("affiliate-rsa", argv[1]) != 0){
        printf("Invalid directory!\n");
        return 2;
    }
    if (strlen(argv[2]) > 100){
        printf("Invalid username!\n");
        return 3;
    }
    for(int i = 0; i < strlen(argv[2]); i++){
        if (!isalnum(argv[2][i])){
            printf("Invalid username!\n");
            return 3;
        }
    }
    setuid(0);
    system("/var/www/html/VPNPortal/gen_cert.sh");
    return 0;
}
