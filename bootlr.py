#!/usr/bin/env python
# coding: utf-8

import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns

from sklearn.utils import assert_all_finite
from sklearn.utils import check_consistent_length
from sklearn.utils import column_or_1d, check_array
from sklearn.utils.multiclass import type_of_target
from sklearn.utils.extmath import stable_cumsum
from sklearn.utils.sparsefuncs import count_nonzero
from sklearn.exceptions import UndefinedMetricWarning
from sklearn.preprocessing import label_binarize
from sklearn.metrics.ranking import _binary_clf_curve, roc_curve

import rpy2
from rpy2.robjects.packages import importr
# import rpy2.robjects.packages as rpackages
# from rpy2.robjects.vectors import StrVector
# from rpy2.robjects import pandas2ri
# utils = rpackages.importr('utils')
# utils.chooseCRANmirror(ind=1)
# packnames = ('binom', 'bootLR')
# utils.install_packages(StrVector(packnames))

from functools import partial
import multiprocessing
from multiprocessing import Pool


# Flatten _openen files
# n is 5UTR length
# t is downstream distance to start codon to include
def openen_label(n, t, f):
    d = pd.read_csv(f, sep='\t', skiprows=2, nrows=(n+t), header=None)
    d = d.set_index(0).stack().to_frame()
    d = d.index.to_frame()
    d.columns = ["Position 'i' centered at the start codon", "Subsegment 'l'"]
    d = d.reset_index()[["Position 'i' centered at the start codon", "Subsegment 'l'"]]
    d["Position 'i' centered at the start codon"] = d["Position 'i' centered at the start codon"] - n
    return d

# Get opening energy at a specific subsegment 'l'
# and position 'i' centered at the start codon
def get_openen(n, t, f1, l, i, f2):
    label = openen_label(n, t, f1)
    d = pd.read_pickle(f2)
    d = d.set_index('id')
    d = d.T
    d = pd.concat([label, d], axis=1)
    o = d.loc[(d["Subsegment 'l'"] == l) & (d["Position 'i' centered at the start codon"] == i)]
    o = o.T[2:]
    o.columns = ['Opening energy']
    return o

def roc(outcomes, prediction):
    fps, tps, thresholds = _binary_clf_curve(outcomes, prediction)
    clf = pd.DataFrame([fps, tps, thresholds]).T
    clf.columns = ['fps', 'tps', 'thresholds']
    clf['fps'] = clf['fps'].astype(int)
    clf['tps'] = clf['tps'].astype(int)
    fpr, tpr, thresholds = roc_curve(outcomes, prediction, drop_intermediate=False)
    r = pd.DataFrame([fpr, tpr, thresholds]).T
    r.columns = ['fpr', 'tpr', 'thresholds']
    df = pd.merge(clf, r, on='thresholds')
    return df

def run_bootlr(P, N, d):
    r = rpy2.robjects.r
    set_seed = r('set.seed')
    set_seed(12345)
    bootLR = importr('bootLR')
    truePos=int(d[1])
    trueNeg=N-int(d[0])
    lr = bootLR.BayesianLR_test(truePos=truePos, totalDzPos=P, 
                                trueNeg=trueNeg, totalDzNeg=N)
    lr = [lr.rx('posLR')[0][0], lr.rx('posLR.ci')[0][0], lr.rx('posLR.ci')[0][1],
         lr.rx('negLR')[0][0], lr.rx('negLR.ci')[0][0], lr.rx('negLR.ci')[0][1]]
    x = d
    x.extend(lr)
    print(x)



d = get_openen(71, 100, 'AaCD00331182_openen', 48, 24, 'openen.pkl')
d = d.reset_index()
d.columns = ['Accession', 'Opening energy']
c = pd.read_csv('class.txt', sep='\t')
c = c.replace(2,1)
d = pd.merge(d, c, on='Accession')
d = roc(d['Class'], -d['Opening energy'])
d = d.values.tolist() #fps tps thresholds fpr tpr

p = Pool(40)
bootlr = partial(run_bootlr, 8780, 2650)
print('fps,', 'tps,', 'thresholds,', 'fpr,', 'tpr,', 'posLR,', 'posLR.ci.lower,', 'posLR.ci.upper,', 'negLR negLR.ci.lower,', 'negLR.ci.upper,')
p.imap_unordered(bootlr, d)
p.close()
p.join()
