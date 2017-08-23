using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.mdo.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaOptions
    {
        public static KeyValuePair<string,ArrayList> parse(String siteId, String[] vistaRtn)
        {
            ArrayList options = new ArrayList();
            for (int i=0; i<vistaRtn.Length; i++)
            {
                String[] flds = StringUtils.split(vistaRtn[i], StringUtils.CARET);
                options.Add(new UserOption(flds[0],flds[1],flds[2]));
            }
            return new KeyValuePair<string,ArrayList>(siteId, options);
        }

        public static String getNumberByIen(KeyValuePair<string, ArrayList> siteOptions, String target)
        {
            for (int i = 0; i < siteOptions.Value.Count; i++)
            {
                VistaOption opt = (VistaOption)siteOptions.Value[i];
                if (opt.Id == target)
                {
                    return opt.Number;
                }
            }
            return "";
        }

        public static String getNumberByName(VistaOption[] siteOptions, String target)
        {
            for (int i = 0; i < siteOptions.Length; i++)
            {
                if (siteOptions[i].Name == target)
                {
                    return siteOptions[i].Number;
                }
            }
            return "";
        }

        public static String getIenByNumber(KeyValuePair<string, ArrayList> siteOptions, String target)
        {
            for (int i = 0; i < siteOptions.Value.Count; i++)
            {
                VistaOption opt = (VistaOption)siteOptions.Value[i];
                if (opt.Number == target)
                {
                    return opt.Id;
                }
            }
            return "";
        }

        public static String getIenByName(KeyValuePair<string, ArrayList> siteOptions, String target)
        {
            for (int i = 0; i < siteOptions.Value.Count; i++)
            {
                VistaOption opt = (VistaOption)siteOptions.Value[i];
                if (opt.Name == target)
                {
                    return opt.Id;
                }
            }
            return "";
        }

        public static void add(KeyValuePair<string, ArrayList> siteOptions, String optNum, String optIen, String optName)
        {
            ((ArrayList)siteOptions.Value).Add(new UserOption(optNum, optIen, optName));
        }

        public static void remove(KeyValuePair<string, ArrayList> siteOptions, String optionNum)
        {
            for (int i = 0; i < siteOptions.Value.Count; i++)
            {
                VistaOption opt = (VistaOption)siteOptions.Value[i];
                if (opt.Number == optionNum)
                {
                    siteOptions.Value.RemoveAt(i);
                    return;
                }
            }
        }

        public static bool hasOption(VistaOption[] siteOptions, String option)
        {
            for (int i = 0; i < siteOptions.Length; i++)
            {
                if (siteOptions[i].Name == option)
                {
                    return true;
                }
            }
            return false;

        }
    }
}
