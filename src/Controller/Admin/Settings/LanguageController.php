<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace App\Controller\Admin\Settings;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController extends BaseAdminController
{

    #[Route('/admin/language', name: 'LanguageNewAdmin')]
    #[Route('/admin/language/{id}', requirements: ['id' => '\d+'], name: 'LanguageAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('settings');

        #### Update
        ###########
        if (!empty($language = Request::getInputCheckEditAccess(Language::class, $id))) {

            if (empty($language->id)) {
                $language = Design::setFlashMessage('add', Language::createOne($language));
            } else {
                Design::setFlashMessage('update', Language::updateOne($language->id, $language));
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('LanguageAdmin', ['id' => $language->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $language = Language::getOne($id);

            if (empty($language->id)) {
                return $this->redirectToRoute('LanguageListAdmin');
            }
        }

        $languageCodes = [
            'aa','ab','ae','af','ak','am','an','ar','as','av','ay','az','ba','be',
            'bg','bh','bi','bm','bn','bo','br','bs','ca','ce','ch','co','cr','cs',
            'cu','cv','cy','da','de','dv','dz','ee','el','en','eo','es','et','eu',
            'fa','ff','fi','fj','fo','fr','fy','ga','gd','gl','gn','gu','gv','ha',
            'he','hi','ho','hr','ht','hu','hy','hz','ia','id','ie','ig','ii','ik',
            'io','is','it','iu','ja','jv','ka','kg','ki','kj','kk','kl','km','kn',
            'ko','kr','ks','ku','kv','kw','ky','la','lb','lg','li','ln','lo','lt',
            'lu','lv','mg','mh','mi','mk','ml','mn','mr','ms','mt','my','na','nb',
            'nd','ne','ng','nl','nn','no','nr','nv','ny','oc','oj','om','or','os',
            'pa','pi','pl','ps','pt','qu','rm','rn','ro','ru','rw','sa','sc','sd',
            'se','sg','si','sk','sl','sm','sn','so','sq','sr','ss','st','su','sv',
            'sw','ta','te','tg','th','ti','tk','tl','tn','to','tr','ts','tt','tw',
            'ty','ug','uk','ur','uz','ve','vi','vo','wa','wo','xh','yi','yo','za',
            'zh','zu'
        ];

        $countryCodes = [
            'AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU',
            'AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BQ',
            'BA','BW','BV','BR','IO','BN','BG','BF','BI','CV','KH','CM','CA','KY',
            'CF','TD','CL','CN','CX','CC','CO','KM','CG','CD','CK','CR','CI','HR',
            'CU','CW','CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE',
            'ET','FK','FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH',
            'GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA',
            'HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP',
            'JE','JO','KZ','KE','KI','KP','KR','KW','KG','LA','LV','LB','LS','LR',
            'LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ',
            'MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA',
            'NR','NP','NL','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK',
            'PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO',
            'RU','RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN',
            'RS','SC','SL','SG','SX','SK','SI','SB','SO','ZA','GS','SS','ES','LK',
            'SD','SR','SJ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO',
            'TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','US','UM','UY','UZ',
            'VU','VE','VN','VG','VI','WF','EH','YE','ZM','ZW'
        ];

        Design::assign('language', $language);
        Design::assign('language_codes', $languageCodes);
        Design::assign('country_codes', $countryCodes);

        return $this->fetchResponse('settings/language.tpl');
    }
}
