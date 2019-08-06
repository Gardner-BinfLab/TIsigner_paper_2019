#### Bhandari BK, Lim CS, Gardner PP. (2019). Highly accessible translation initiation sites are predictive of successful heterologous protein expression. bioRxiv doi: 
- This repository contains the scripts and Jupyter notebooks to reproduce the results and figures of this preprint. The source code of TIsigner webserver is available [here](https://github.com/Gardner-BinfLab/TIsigner).
- Dependencies can be installed using Anaconda3. For example,
```conda install -c bioconda viennarna```. ViennaRNA can also be installed according to the instructions [here](https://www.tbi.univie.ac.at/RNA/documentation.html#install).
- IXnos requires python2 to run (https://github.com/lareaulab/iXnos/)
- openen.py is a wrapper for RNAplfold using multiple processes. It is useful to calculate the opening energy of multi-fasta sequences. The output can be analysed as in Fig1_2_S1_S2.ipynb

```console
$ python openen.py -h
usage: openen.py [-h] -s STR [-U STR/INT] [-x] [-W INT] [-u INT] [-S] [-n INT]
                 [-t INT] [-e] [-i INT] [-l INT] [-r] [-o STR] [-p INT]

RNAplfold wrapper using multiprocesses

optional arguments:
  -h, --help            show this help message and exit
  -s STR, --sequence STR
                        Sequences in fasta or csv format
  -U STR/INT, --utr STR/INT
                        Use an integer if 5UTR presence, e.g., -U 1. Use your
                        own 5UTR sequence if your plasmid backbone is not of
                        pET vector. Default = GGGGAATTGTGAGCGGATAACAATTCCCCTCT
                        AGAAATAATTTTGTTTAACTTTAAGAAGGAGATATACAT
  -x, --execute         Run RNAplfold multiprocessing
  -W INT, --winsize INT
                        Average the pair probabilities over windows of given
                        size. An RNAplfold option. Default = 210
  -u INT, --ulength INT
                        Compute the mean probability that subsegments of
                        length 1 to a given length are unpaired. An RNAplfold
                        option. Default = 210
  -S, --stack           Stack _openen dataframes to single-column dataframes,
                        concatenate them as a single pandas dataframe and
                        output it as a .pkl pickle file. Requires i and j
                        options
  -n INT, --utrlength INT
                        The length of 5UTR. Related to option -S and -e.
                        Default = 71
  -t INT, --distance INT
                        Downstream distance to start codon to include when
                        stacking. Related to option -S. Default = 100
  -e, --parse           Parsing _openen dataframes to get opening energy of
                        unpaired subsegments. Requires i and l options
  -i INT, --ipos INT    Position i centered at start codon of an input
                        sequence. Related to option -e. Default = 18
  -l INT, --length INT  Subsegment l as in _openen file. Related to option -e.
                        Default = 48
  -r, --remove          Remove _openen and .ps files
  -o STR, --output STR  Output file name for .pkl. Related to -S. Default =
                        openen
  -p INT, --processes INT
                        Number of processes to spawn. Default = half of the
                        number of CPU
```
© [Bikash Kumar Bhandari](https://bkb3.github.io), [Chun Shem Lim](https://github.com/lcscs12345), [Paul P Gardner](https://github.com/ppgardne) (2019)
