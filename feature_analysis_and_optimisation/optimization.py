#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri Mar 29 13:47:59 2019

@author: bikash
"""

import pickle
from functools import lru_cache
import numpy as np
import functions, features




class Optimizer:
    '''Optimizes the sequence using simulated annealing
    '''


    def __init__(self, seq=None, rf_model=None):
        self.seq = seq.upper()
        self.rf_model = self.validate_model(rf_model)
#        self.nbackgnd = nbackgnd
#        self.back_feat = None
#        self.back_mean_sd = None
        self.annealed_seq = None


    @staticmethod
    def validate_model(model):
        '''Checks if the given random forest classifier is usable.
        '''
        try:
            with open(model, 'rb') as file:
                rf = pickle.load(file)
        except TypeError:
            try:
                model.feature_importances_
                rf = model
            except AttributeError:
                raise NotImplementedError("Unknown or empty model.")
        return rf


    def background_features(self):
        '''Calculates features for background sequences.
        Currently, the background is random synonymous sequences.
        '''
        back_df = functions.syn_background(seq=self.seq, n=self.nbackgnd)
        back_df_feat = features.AnalyzeDataFrameFeatures(input_file=back_df)
        back_mean_sd = back_df_feat.mean_and_sd()
        self.back_feat = back_df_feat.all_features
        self.back_mean_sd = back_mean_sd
        return back_mean_sd


    @lru_cache(maxsize=128, typed=True)
    def cost_function_old(self, new_seq=None):
        '''Calculates z scores of a given sequence based upon a group of
        sequences given to Optimizer class.
        '''
        if new_seq is None:
            seq = self.seq
        else:
            seq = new_seq
        ftrs = features.AnalyzeSequenceFeatures(seq=seq)
        if self.back_mean_sd is None:
            self.back_mean_sd = self.background_features()
        df = self.back_mean_sd
        df['weights'] = [1]*df.shape[0]
        df['seq'] = ''
        df.loc['cai', 'seq'] = ftrs.cai()
        df.loc['gc_cont', 'seq'] = ftrs.gc_cont()
        df.loc['sec_str', 'seq'] = ftrs.sec_str()
        df.loc['avd', 'seq'] = ftrs.avoidance()
        df.loc['accs', 'seq'] = ftrs.access_calc()
        df['z_'] = (df['seq'] - df['mean'])/df['sd']
        df['weighted_z'] = df['z_'] * df['weights']
        total_z = df.loc[['cai', 'sec_str', 'avd'], 'weighted_z'\
                         ].sum() - df.loc[['gc_cont', 'accs'], \
                         'weighted_z'].sum()
        return total_z


    @lru_cache(maxsize=128, typed=True)
    def cost_function(self, new_seq=None):
        '''Calculates rf probability for each classifier class.
        '''
        if new_seq is None:
            seq = self.seq
        else:
            seq = new_seq
        analysis_ = features.AnalyzeSequenceFeatures(seq=seq)
        cai = analysis_.cai()
        gc_cont = analysis_.gc_cont()
        sec_str =  analysis_.sec_str()
        avd = analysis_.avoidance()
        accs = analysis_.access_calc()
        all_feat = [cai, gc_cont, sec_str, avd, accs] #['CAI','G+C (%)','STR(-30:30)','Avoidance','Accessibility']
        rfc = self.rf_model
        cost = rfc.predict_proba([all_feat])[0][1]
        return cost


    def simulated_anneal(self, itr=50):
        '''
        preforms a simulated annealing
        '''
        seq = self.seq
        if itr < 10:
            itr = 10
        niter = itr
        temp = np.geomspace(1, 0.00001, niter)
        length = functions.sequence_length(seq)
        num_of_subst = [int((length-5)*np.exp(-_/int(niter/10))+5) \
                         for _ in range(niter)]
        scurr = seq
        sbest = seq
        print("\nSimulated annealing progress:")
        print("iter\tcurrent cost\tbest cost")
        print("====\t============\t=========")
        for i in range(niter):
            t = temp[i]
            snew = functions.substitute_codon(sbest, num_of_subst[i])
            if self.cost_function(snew) >= self.cost_function(scurr):
                scurr = snew
                if self.cost_function(scurr) >= self.cost_function(sbest):
                    sbest = snew
            elif np.exp(-(self.cost_function(scurr)-self.cost_function(snew))\
                        /t) <= np.random.rand(1)[0]:
                scurr = snew
            print('\r%s\t %0.8f\t %0.8f\r'%(i, self.cost_function(scurr), \
                                             self.cost_function(sbest)), \
                                                end='\r')
            if self.cost_function(sbest) == 1:
                break
        annealed_seq = sbest
        self.annealed_seq = annealed_seq
        return annealed_seq

    def simulated_anneal_primer(self, itr=100):
        '''
        preforms a simulated annealing
        '''
        seq = self.seq
        ncodons = 5
        niter = itr
        temperatures = np.geomspace(ncodons, 0.00001, niter)
        num_of_subst = [int((ncodons-1)*np.exp(-_/int(niter/2))+1) \
                         for _ in range(niter)]
        scurr = seq
        sbest = seq
        print("\nSimulated annealing progress:")
        print("iter\tcurrent cost\tbest cost")
        print("====\t============\t=========")
        for idx, temp in enumerate(temperatures):
            snew = functions.substitute_codon_primer(sbest, ncodons, num_of_subst[idx])
            if self.cost_function(snew) >= self.cost_function(scurr):
                scurr = snew
                if self.cost_function(scurr) >= self.cost_function(sbest):
                    sbest = snew
            elif np.exp(-(self.cost_function(scurr)-self.cost_function(snew))\
                        /temp) <= np.random.rand(1)[0]:
                scurr = snew
            print('\r%s\t %0.8f\t %0.8f\r'%(idx, self.cost_function(scurr), \
                                             self.cost_function(sbest)), \
                                                end='\r')

            if self.cost_function(sbest) == 1:
                break
        annealed_seq = sbest
        self.annealed_seq = annealed_seq
        return annealed_seq



