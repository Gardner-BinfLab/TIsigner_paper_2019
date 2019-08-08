#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Sat Mar 23 11:30:45 2019

@author: bikash
"""

import re
import os
import warnings
import secrets
import string
from multiprocessing import Pool, cpu_count
from subprocess import run, PIPE, DEVNULL
import pandas as pd
import numpy as np
#from avoidance2 import data, features
import features, data


def progress(iteration, total, message=None):
    '''Simple progressbar
    '''
    if message is None:
        message = ''
    bars_string = int(float(iteration) / float(total) * 50.)
    print("\r|%-50s| %d%% (%s/%s) %s "% ('█'*bars_string+ "░" * \
                                     (50 - bars_string), float(iteration)/\
                                     float(total) * 100, iteration, total, \
                                     message), end='\r', flush=True)

    if iteration == total:
        print('\nCompleted!')


def fasta_reader(file):
    '''Converts .fasta to a pandas dataframe with accession as index
    and sequence in a column 'sequence'
    '''
    fasta_df = pd.read_csv(file, sep='>', lineterminator='>', header=None)
    fasta_df[['Accession', 'Sequence']] = fasta_df[0].str.split('\n', 1, \
                                        expand=True)
#    fasta_df['accession'] = '>'+fasta_df['accession']
    fasta_df['Accession'] = fasta_df['Accession']
    fasta_df['Sequence'] = fasta_df['Sequence'].replace('\n', '', regex=True).\
                            astype(str).str.upper().replace('U', 'T')
    total_seq = fasta_df.shape[0]
    fasta_df.drop(0, axis=1, inplace=True)
#    fasta_df.set_index('Accession', inplace=True)
    fasta_df = fasta_df[fasta_df.Sequence != '']
    fasta_df = fasta_df[fasta_df.Sequence != 'NONE']
    final_df = fasta_df.dropna()
    remained_seq = final_df.shape[0]
    if total_seq != remained_seq:
        warnings.warn("{} sequences were removed due to inconsistencies in"
                      "provided file.".format(total_seq-remained_seq))
    return final_df


def csv_reader(file):
    '''Read csv and returns sequences in uppercase
    '''
    df = pd.read_csv(file)
    if df.columns.str.contains('seq|sequence|sequences|Sequence', regex=True).any():
        pass
    else:
        df = pd.read_csv(file, skiprows=1, header=None)
        if len(df.columns) != 2:
            raise IndexError("Please ensure your CSV has accession and sequence\
                             column only!")
        if bool(re.search(r'\d', str(df[0][0]))) is True:
            df = df.rename(columns={0: 'Accession', 1: 'Sequence'})
        else:
            df = df.rename(columns={0: 'Sequence', 1: 'Accession'})
        df['Sequence'] = df['Sequence'].str.upper().replace('U', 'T')
    total_seq = df.shape[0]
    final_df = df
    if df['Sequence'].isnull().any():
        final_df = df.dropna()
    remained_seq = final_df.shape[0]
    if total_seq != remained_seq:
        warnings.warn("{} sequences were removed due to inconsistencies in"
                      "provided file.".format(total_seq-remained_seq))
    return final_df



def file_check(file):
    '''Check wether we are given a proper file or not and parse that file
    accordingly.
    '''
    try:
        ext = os.path.splitext(file)[1]
        if ext.lower() in ('.fasta', '.fa'):
            seq_df = fasta_reader(file)
        elif ext.lower() in '.csv':
            seq_df = csv_reader(file)
        else:
            raise NotImplementedError("We read csv or fasta files only.")
        return seq_df
    except TypeError:
        if isinstance(file, pd.DataFrame):
            return file
        else:
            raise NotImplementedError("Either file is bad or empty.")


def sequence_length(seq):
    '''
    Returns length of sequence in multiple of 3 by chopping extra positions
    '''
    if len(seq)%3 != 0:
        length = (len(seq)- len(seq)%3)
    else:
        length = len(seq)
    return length


def splitter(seq, length=None):
    '''
    Split sequence to codons
    '''
    seq = seq.upper()
    if length is None:
        try:
            length = sequence_length(seq)
        except sequence_length(seq) == 0:
            raise ValueError("Too short sequence.")

    length = length - length%3
    if length == 0:
        raise ValueError("Too short distance to look for.")
    split_func = lambda seq, n: [seq[i:i+n] for\
                                    i in range(0, length, n)]
    return split_func(seq, 3)


def rms_check(seq_df, rms=None):
    '''Restriction modification sites checking.
    '''
    #rms = site you want to check (multiple sites can be joined by pipe |)
    #If no sites are given, we will check for default sites for AarI, BsaI,
    #BsmBI and a possible terminator TTTTT.
    if 'sequence' not in seq_df.columns:
        raise IndexError("Given dataframe has no column 'sequence'.")
    if rms is None:
        warnings.warn("Checking for default restriction modification sites "
                      "(AarI, BsaI, BsmBI) and a possible terminator"
                      " 'TTTTT'.")
        rms = 'TTTTT|CACCTGC|GCAGGTG|GGTCTC|GAGACC|CGTCTC|GAGACG'
    seq_df['rms'] = seq_df.sequence.str.contains(rms, regex=True)
    total_seq = seq_df.shape[0]
    filtered_df = seq_df[seq_df.rms == False].reset_index(drop=True)
    filtered_df.drop('rms', axis=1, inplace=True)
    remained_seq = filtered_df.shape[0]
    if total_seq != remained_seq:
        warnings.warn("{} sequences were removed because we found restriction"
                      " modification sites.".format(total_seq-remained_seq))
    return filtered_df


#optimization functions


def rand_background(seq, n=1000):
    '''Random background
    '''
    length = sequence_length(seq)
    codons = [k for k, v in data.codon2aa.items()]
    backgnd_seq = pd.DataFrame({'sequence':\
                                [''.join(np.random.choice(codons, length))\
                                            for _ in range(n)]})
    return backgnd_seq


def syn_background(seq, n=1000):
    '''Randon synonymous background
    '''
    start = seq[:3]
    stop = seq[-3:]
    seq = seq[3:-3].upper()
    length = sequence_length(seq)
    codons = splitter(seq, length)
    syn_seq = []
    for j in range(n):
        chain = ''
        for ind, codon in enumerate(codons):
            possible_codons = data.AA_TO_CODON[data.CODON_TO_AA[codon]]
            chain += np.random.choice(possible_codons)
        syn_seq.append(start + chain + stop)
    backgnd_seq = pd.DataFrame({'sequence':syn_seq})
    total_seq = backgnd_seq.shape[0]
    final_df = backgnd_seq.drop_duplicates().reset_index(drop=True)
    remained_seq = final_df.shape[0]
    if total_seq != remained_seq:
        warnings.warn("Out of {} generated sequences, {} duplicate "
                      "sequences were removed."\
                      .format(total_seq, total_seq-remained_seq))
    return final_df


def parallelize_df(df, func):
    '''parallelizes operations on a dataframe by splitting it to chunks
    '''
    partitions = cpu_count()
    df_split = np.array_split(df, partitions)
    pool = Pool(partitions)
    results = pd.concat(pool.map(func, df_split))
    pool.close()
    pool.join()
    return results


def interaction_calc(seq):
    '''Runs RNAup and returns its output.
    '''
    proc = run(['RNAup', '-b', '-o'], stdout=PIPE, stderr=DEVNULL, \
               input=seq, encoding='utf-8')
    return str(proc.stdout).replace("\\n", " ").replace("b'", "")


def rnaup_result_parser(raw_result_list, mrna_dataframe=None):
    '''Returns parsed interaction and a dataframe of all interactions
    as a tuple, first element is the parsed scores only, second is
    full table of results with sequences accession and RNAup output.
    You will rarely need the second element. It is mainly for debug purposes.
    note: this function is for one mrna vs one or multiple ncrna
    '''
    #check if we are supplied a list (like for many sequences)
    #or single item like(for one seq vs several other seq)
    try:
        results_list = raw_result_list.copy()
    except AttributeError:
        results_list = [raw_result_list]


    interaction_df = pd.DataFrame({'unparsed_results':results_list})
    interaction_df[['Accession', 'RNAup_output']] = interaction_df\
                                                   ['unparsed_results']\
                                                   .str.split(':break', 1, \
                                                              expand=True)
    results_parsed_df = pd.Series(interaction_df['RNAup_output']\
                                .str.extractall(r'((?<=\:).*?(?==))')[0])\
                                .str.split(pat='(', n=-1, expand=True)\
                                .drop(0, 1).astype(np.float64).unstack()
    if mrna_dataframe is not None:
        results_parsed_df.columns = mrna_dataframe.index
    result_df = pd.concat([interaction_df, results_parsed_df], axis=1)
    return results_parsed_df, result_df


def accession_gen():
    '''Random accession numbers
    '''
    rand_string = ''.join(secrets.choice(string.ascii_uppercase + \
                                        string.digits) for _ in range(10))
    accession = '>' + rand_string + '\n'
    return accession, rand_string


def sequence_df_features(input_df=None):
    '''Calculates all features of sequences in a dataframe.
    '''
    if input_df is None:
        raise ValueError("No input files to calculate features.")
    input_df['analyze'] = input_df['Sequence'].\
                                apply(features.AnalyzeSequenceFeatures)
    input_df['CAI'] = input_df['analyze'].apply(lambda x: \
                   x.cai())
    input_df['tAI'] = input_df['analyze'].apply(lambda x: \
                   x.tai())
    input_df['G+C (%)'] = input_df['analyze'].\
                                apply(lambda x: x.gc_cont())
    input_df['STR(-30:30)'] = input_df['analyze'].\
                                apply(lambda x: x.sec_str())
    input_df['Avoidance_unparsed'] = input_df['analyze'].\
                        apply(lambda x: x.avoidance())
    input_df['Accessibility'] = input_df['analyze'].\
                                apply(lambda x: x.access_calc())
    return input_df


def substitute_codon(seq, num_of_subst=10):
    '''randomly substitute codons along the sequence at random positions
    '''
    start = seq[:3]
    stop = seq[-3:]
    #num_of_subst = np.random.choice(length)
    new_seq = seq[3:-3]
    length = sequence_length(new_seq)
    for i in range(num_of_subst):
        codons = splitter(new_seq, length)
        subst_codon_position = np.random.choice(list(range(len(codons)))) 
        subst_synonymous_codons = data.aa2codon[\
                                                data.codon2aa[codons[\
                                                subst_codon_position]]]
        subst_codon = np.random.choice(subst_synonymous_codons)
        new_seq = new_seq[:subst_codon_position*3]+ subst_codon +\
                    new_seq[subst_codon_position*3+3:]
          
    return start + new_seq + stop

def substitute_codon_primer(seq, ncodons, nsubst):
    '''randomly substitute codons along the sequence at random positions
    partial substitution for intial n codons after ATG
    '''
    seq = seq.upper()
    num_nts = (ncodons+1)*3 #1 is for start codon ATG
    start = seq[:3]
    new_seq = seq[3:num_nts]
    length = sequence_length(new_seq)
    for _ in range(nsubst):
        codons = splitter(new_seq, length)
        subst_codon_position = np.random.choice(list(range(len(codons))))
        subst_synonymous_codons = data.aa2codon[data.codon2aa[codons[\
                                                subst_codon_position]]]
        subst_codon = np.random.choice(subst_synonymous_codons)
        new_seq = new_seq[:subst_codon_position*3]+ subst_codon +\
                    new_seq[subst_codon_position*3+3:]

    return start + new_seq + seq[num_nts:]