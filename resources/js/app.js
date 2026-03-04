import './bootstrap';
import * as ZXingBrowser from '@zxing/browser';
import { NotFoundException } from '@zxing/library';

window.ZXingBrowser = { ...ZXingBrowser, NotFoundException };
