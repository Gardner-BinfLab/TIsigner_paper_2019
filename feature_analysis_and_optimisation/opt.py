#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Tue May  7 20:56:34 2019

@author: bikash
"""

import os
#import sys
import secrets
import string
import tempfile
import re
#import warnings
from subprocess import run, PIPE, DEVNULL
from functools import lru_cache
import numpy as np
import pandas as pd
import data









class Optimizer:
    '''Optimizes the given sequence by minimizing accessibility

    Args:
        seq = Your sequence.
        ncodons = Number of codons to substitute at 5' end. Default (5)
        utr = UTR of your choice. Default = pET21
        niter = Number of iterations for simulated annealing. Default 1000
        threshold = The value of accessibility you're aiming for. If we get
                     this value, simulated annealing will stop. Else, we
                     will run to specified iterations and give the sequence
                     with minimum possible accessibility.

    '''
    #np.random.seed(data.RANDOM_SEED)

    def __init__(self, seq, ncodons=None, utr=None, niter=None,\
                 threshold=None, plfold_args=None, rms_sites=None):
        self.seq = seq
        self.ncodons = ncodons
        self.utr = utr
        self.niter = niter
        self.threshold = threshold
        self.annealed_seq = None #result of simulated annealing
        self.plfold_args = plfold_args
        self.rms_sites = rms_sites

    @staticmethod
    def sequence_length(seq):
        '''
        Returns length of sequence in multiple of 3 by chopping extra positions
        '''
        if len(seq)%3 != 0:
            length = (len(seq)- len(seq)%3)
        else:
            length = len(seq)
        return length


    @staticmethod
    def accession_gen():
        '''Random accession numbers
        '''
        rand_string = ''.join(secrets.choice(string.ascii_uppercase + \
                                            string.digits) for _ in range(10))
        accession = '>' + rand_string + '\n'
        return accession, rand_string


    @staticmethod
    def splitter(seq, length=None):
        '''
        Split sequence to codons
        '''
        seq = seq.upper()
        if length is None:
            try:
                length = Optimizer.sequence_length(seq)
            except Optimizer.sequence_length(seq) == 0:
                raise ValueError("Too short sequence.")

        length = length - length%3
        if length == 0:
            raise ValueError("Too short distance to look for.")
        split_func = lambda seq, n: [seq[i:i+n] for\
                                        i in range(0, length, n)]
        return split_func(seq, 3)


    @staticmethod
    def substitute_codon(seq, ncodons, nsubst, rms_sites=None, rand_state=None):
        '''randomly substitute codons along the sequence at random positions
        partial substitution for intial n codons after ATG
        '''
        if rand_state is None:
            rand_state = np.random.RandomState(12345)
        #print(rand_state, "from substcodons")
        if rms_sites is None:
            rms_sites = 'TTTTT|CACCTGC|GCAGGTG|GGTCTC|GAGACC|CGTCTC|GAGACG'
        seq = seq.upper()
        #num_nts = (ncodons+1)*3 #1 is for start codon ATG
        #for web version ncodons is counted from start so no need to add 1
        num_nts = (ncodons)*3
        start = seq[:3]
        new_seq = seq[3:num_nts]
        length = Optimizer.sequence_length(new_seq)
        #print(rms_sites)


        while True:
            for _ in range(nsubst):
                codons = Optimizer.splitter(new_seq, length)
                subst_codon_position = rand_state.choice(list(range(len(codons))))
                subst_synonymous_codons = data.AA_TO_CODON[data.CODON_TO_AA[codons[\
                                                        subst_codon_position]]]
                subst_codon = rand_state.choice(subst_synonymous_codons)
                new_seq = new_seq[:subst_codon_position*3]+ subst_codon +\
                            new_seq[subst_codon_position*3+3:]
            if not re.findall(rms_sites, new_seq):
                break

        return start + new_seq + seq[num_nts:]


    @lru_cache(maxsize=128, typed=True)
    def accessibility(self, new_seq=None):
        '''Sequence accessibility
        '''
        tmp = os.path.join(tempfile.gettempdir(), 'plfold')
        try:
            os.makedirs(tmp)
        except FileExistsError:
            pass

        nt_pos = 24
        subseg_length = 48
        if self.utr is None:
            utr = data.pET21_UTR
        else:
            utr = self.utr.upper()

        if new_seq is None:
            seq = self.seq
        else:
            seq = new_seq


        all_args = ['RNAplfold'] + self.plfold_args.split(' ')
        #print(all_args)
        sequence = utr + seq
        seq_accession, rand_string = Optimizer.accession_gen()
        input_seq = seq_accession + sequence
        run(all_args, stdout=PIPE, stderr=DEVNULL, input=input_seq, cwd=tmp, \
                    encoding='utf-8')
        out1 = '/' + rand_string + '_openen'
        out2 = '/' + rand_string + '_dp.ps'
#        open_en43 = pd.read_csv(tmp+out1, sep='\t', skiprows=2, header=None)\
#                    [43][len(utr):len(utr)+alength].sum()
        open_en = pd.read_csv(tmp+out1, sep='\t', skiprows=2, header=None)\
                    [subseg_length][len(utr) + nt_pos - 1] #49th column.


        os.remove(tmp+out1)
        os.remove(tmp+out2)
        return open_en


    def simulated_anneal(self, rand_state):
        '''
        preforms a simulated annealing
        Returns:
            Optimized sequence with its accessibility
            New: optimises posterior probability using accessibility

        '''
        #print(rand_state, "from simanneal")
        seq = self.seq
        if self.ncodons is None:
            ncodons = 10
        else:
            ncodons = self.ncodons
        if self.niter is None:
            niter = 25
        else:
            niter = self.niter
        rms_ = self.rms_sites
        temperatures = np.geomspace(ncodons, 0.00001, niter)
        num_of_subst = [int((ncodons-1)*np.exp(-_/int(niter/2))+1) \
                         for _ in range(niter)] #floor returned float so changed to int
        scurr = seq
        sbest = seq
        #print("\nSimulated annealing progress:")
        #print("iter\tcurrent cost\tbest cost")
        #print("====\t============\t=========")
        for idx, temp in enumerate(temperatures):
            snew = self.substitute_codon(sbest, ncodons, num_of_subst[idx], \
                                         rms_sites=rms_, rand_state=rand_state)
            if Optimizer.accessibility(self, snew)/1000 <= \
            Optimizer.accessibility(self, scurr)/1000:
                scurr = snew
                if Optimizer.accessibility(self, scurr)/1000 <= \
                Optimizer.accessibility(self, sbest)/1000:
                    sbest = snew
            elif np.exp(-(Optimizer.accessibility(self, snew)/1000)- \
                          (Optimizer.accessibility(self, scurr)/1000)\
                        /temp) >= np.random.rand(1)[0]:
                scurr = snew
            #print('\r%s\t %0.8f\t %0.8f\r'%(\
            #                    idx, get_prob_pos(Optimizer.accessibility(self, scurr)), \
            #                    get_prob_pos(Optimizer.accessibility(self, sbest))), \
            #                                    end='\r')

            if self.threshold is not None and \
            Optimizer.accessibility(self, sbest) <= self.threshold:
                break


        annealed_seq = sbest
        self.annealed_seq = (annealed_seq, \
                             Optimizer.accessibility(self, sbest))
        return annealed_seq, Optimizer.accessibility(self, sbest)






