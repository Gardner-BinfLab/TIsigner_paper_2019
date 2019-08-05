#!/usr/bin/env python2
# -*- coding: utf-8 -*-

from __future__ import print_function
import os
import sys
import time
import argparse
import itertools
import subprocess
import numpy as np
import pandas as pd
import multiprocessing
import threading
from threading import Semaphore
from datetime import datetime
from multiprocessing import Pool
from iXnos import interface as inter
from iXnos import optimizecodons as opt


def valid_file(param):
    base, ext = os.path.splitext(param)
    if ext.lower() not in ('.csv', '.fasta', '.fa', '.fas', '.fna'):
        raise argparse.ArgumentTypeError('File must have a fasta or csv extension')
    return param



def check_arg(args=None):
    parser = argparse.ArgumentParser(description='Scoring sequences using an iXnos neural network model using multiprocesses')
    parser.add_argument('-i', '--input',
                        type=valid_file,
                        metavar='STR',
                        help='Sequences in fasta or csv format',
                        required='True')
    parser.add_argument('-d', '--nn_dir',
                        type=str,
                        metavar='STR',
                        help='Path for lasagne neural network model',
                        required='True')
    parser.add_argument('-e', '--epoch',
                        type=int,
                        metavar='INT',
                        help='Epoch of the model',
                        required='True')
    parser.add_argument('-o', '--output',
                        metavar='STR',
                        default='iXnos', 
                        help='Output file name. Default = iXnos')
    parser.add_argument('-p', '--processes',
                        type=int,
                        metavar='INT',
                        help='Number of processes to spawn. Default = total number of CPU')

    results = parser.parse_args(args)
    return (results.input, results.nn_dir, results.epoch, results.output, results.processes)


def progress(iteration, total):   
    bars_string = int(float(iteration) / float(total) * 50.)
    sys.stdout.write(
        "\r|%-50s| %d%% (%s/%s)" % (
            '█'*bars_string+ "░" * (50 - bars_string), float(iteration) / float(total) * 100,
            iteration,
            total
        ) 
    )
    sys.stdout.flush()
    if iteration == total:
        print(' Completed!')


        
def fasta_to_dataframe(f):
    fasta_df = pd.read_csv(f,sep='>', lineterminator='>',header=None)
    fasta_df[['accession','sequence']]=fasta_df[0].str.split('\n', 1, expand=True)
    fasta_df['accession'] = fasta_df['accession']
    fasta_df['sequence'] = fasta_df['sequence'].replace('\n','', regex=True)
    fasta_df.drop(0, axis=1, inplace=True)
    fasta_df.set_index('accession',inplace=True)
    fasta_df = fasta_df[fasta_df.sequence != '']
    final_df = fasta_df.dropna()
    return final_df
    


def nn_score(cod_seq):
    nt_feats = True
    rel_cod_idxs = range(-3,3)
    rel_nt_idxs = [cod_idx * 3 + i for cod_idx in rel_cod_idxs for i in range(3)]
    my_nn = inter.load_lasagne_feedforward_nn(d, e)
    cds_score = opt.score_cod_seq_full(cod_seq, my_nn, rel_cod_idxs, rel_nt_idxs)
    return cod_seq, cds_score



def main():
    base,ext = os.path.splitext(i)
    if ext.lower() in ('.fasta', '.fa', '.fas', '.fna'):
        seq = fasta_to_dataframe(i).reset_index()
    else:
        seq = pd.read_csv(i, skiprows=1, header=None)
    
    startTime = datetime.now()
    
    cds = seq['sequence'].tolist()
    mp = Pool(p)
    results = []
    progress(0, len(cds))
    for j in mp.imap_unordered(nn_score, cds):
        results.append(j)
        progress(len(results), len(cds))

    scores = list(results)
    scores = pd.DataFrame(scores)
    scores.columns = ['sequence', 'iXnos']
    d = pd.merge(seq, scores, on='sequence').drop('sequence', axis=1)
    scores = pd.DataFrame(d['iXnos'].tolist()).mean(axis=1, skipna=True)
    d = pd.concat([d, scores],axis=1).drop('iXnos', axis=1)
    d.columns = ['Accession', 'iXnos']
    filename = o + '.out'
    d.to_csv(filename, sep='\t', index=False, encoding='utf-8')
    
    print('\nWe took', datetime.now() - startTime, 'to finish the task!')
 

if __name__ == "__main__":
    i, d, e, o, p = check_arg(sys.argv[1:])
    if p is None:
        p = multiprocessing.cpu_count()
    main()
