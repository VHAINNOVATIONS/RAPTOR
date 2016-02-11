# .bashrc

# Source global definitions
if [ -f /etc/bashrc ]
then
  . /etc/bashrc
fi

# User specific aliases and functions
if ( tty -s )
then
  csession cache -U cacheinv "^ZU"
  exit
fi
