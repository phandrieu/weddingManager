-- insertion complète des chants dans la table song
use app; 
INSERT INTO song (type_id, name, preview_url, lyrics) VALUES
-- ACCLAMATION ÉVANGILE (type_id = 5)
(5, 'Alleluia - Messe de Saint Paul', 'https://youtu.be/-PqxDNXqHZg?si=x9-WQoVxfPGARRWL', NULL),
(5, 'Alleluia - Psaume 117', 'https://youtu.be/ndI0r-LmMKk?si=npStse1jtu9BueHr', NULL),
(5, 'Alleluia - Taize', 'https://youtu.be/az4jBmvd3mc?si=iI7ny_MKzp__zQvv', NULL),
(5, 'Alleluia - Messe de Saint Boniface', 'https://youtu.be/q2G5xXRyGSg?si=xLL7IIRhjtAZqnIR', NULL),
(5, 'Alleluia - Messe de Saint Jean', 'https://youtu.be/J14RLPPHWmA?si=9dYSdh14E-HWTuiQ', NULL),
(5, 'Alleluia - Messe du Frat', 'https://youtu.be/kozmpe-qGcs?si=5ZvEUkibampuK4nl', NULL),
(5, 'Alleluia - Messe de la Grâce', 'https://youtu.be/JAleY40oNsI?si=rWTwn8RRkoOVUmgd', NULL),
(5, 'Alleluia - Messe de Saint Paul', 'https://youtu.be/-PqxDNXqHZg?si=7F483Z-iFJTjvnLC', NULL),

-- CHANT À L'ESPRIT (type_id = 7)
(7, 'Esprit de lumière, esprit créateur', 'https://youtu.be/H8vFcoTxezI?si=4tJkijSWTPSYqKN3', NULL),
(7, 'Inonde ce lieu de ta présence', 'https://youtu.be/snKoJTmVrxg?si=VDhuxFDWffiIJuJL', NULL),
(7, 'Saint-Esprit (voici mon coeur)', 'https://youtu.be/3f6lVPH7MnY?si=FYopm98LbD2k6LYD', NULL),
(7, 'Viens esprit de sainteté', 'https://youtu.be/aoz425unmew?si=qZpe_-ozRkLePtgD', NULL),
(7, 'Jésus, toi qui a promis', 'https://youtu.be/X8TdQeThRbg?si=gddVNOnIvxl8rS5X', NULL),
(7, 'Feu dévorant', 'https://youtu.be/RTaEMQgrNqo?si=VoP8_ASTYgDE_3v8', NULL),
(7, 'À jamais tu es saint', 'https://youtu.be/v2iRuO_u4aY?si=-KGpG0p8K19z3anV', NULL),
(7, 'Yeshua', 'https://youtu.be/ivUb1K0B0zE?si=J0yu4DntpIoW0_C0', NULL),
(7, 'Oceans', 'https://youtu.be/pR2yEnMpYmE?si=rL4fZ_vCdzJ8-U_o', NULL),

-- CHANT À LA VIERGE (type_id = 18)
(18, 'Couronnée d''étoiles', 'https://youtu.be/OvXIv8tKbgE?si=IyCbTYc-OoWbCGrd', NULL),
(18, 'Ave Maria', 'https://youtu.be/EgNozp8Q4QY?si=qfjsc9F8KVdZ8vdO', NULL),
(18, 'Ô mère bien aimée', 'https://youtu.be/54xs4rJr3eg?si=xhYJ_3Vvv1G749vi', NULL),
(18, 'Regarde l''étoile', 'https://youtu.be/6dlCmAWZ8q4?si=elpO3l1rWs3X0qik', NULL),

-- CHANT APRÈS L'ÉCHANGE DES CONSENTEMENTS (type_id = 10)
(10, 'Magnificat (Taize)', 'https://youtu.be/ijHz0HfRt10?si=f2DpbTLUft6K2pRT', NULL),
(10, 'Chantez avec moi le Seigneur', 'https://youtu.be/0i59nz7iNnw?si=09s4ne2_JhNUvvmm', NULL),
(10, 'Evenou Shalom', 'https://youtu.be/3YoTvPYCJbE?si=XZbiTAiGxbv4FMGX', NULL),
(10, 'Rendons gloire à notre Dieu', 'https://youtu.be/3lpgygl9Uq0?si=MUir9rR-UAceM4hu', NULL),
(10, 'Pour tes merveilles', 'https://youtu.be/FxAFXXa2310?si=4jV7uwnVgrJ2YLMn', NULL),

-- CHANT D'ENTRÉE (type_id = 2)
(2, 'Bienvenue', 'https://youtu.be/MiSEn6a5W0Y?si=5TJZ-SfmABn_kYz8', NULL),
(2, 'Que vive mon âme à te louer (C513)', 'https://youtu.be/SUvjVNLtwhY?si=ZeucuxJqzY7zXKE1', NULL),
(2, 'Jubliez, criez de joie', 'https://youtu.be/XvGpsEsVEPg?si=Tn3xgqLvnR6MSXD1', NULL),
(2, 'Écoute ton Dieu t''appelle', 'https://youtu.be/bXa5gJtrdeg?si=V-X5a0Auk0A3VuYO', NULL),
(2, 'Venez chantons notre Dieu (A509)', 'https://youtu.be/XGZeLG0xLb4?si=abeRl33fH1OOlNWP', NULL),
(2, 'Que ma bouche chante ta louange', 'https://youtu.be/onPVN4Mu6bg?si=WV3WLSLv-Ie6T-DI', NULL),
(2, 'Chantez priez célébrez le Seigneur (A40-73)', 'https://youtu.be/UxJnCBp6HkA?si=DPvQ_yyLYyTCIq16', NULL),
(2, 'Qu''exulte tout l''univers (DEV 44-72)', 'https://youtu.be/XKFnqWFGScA?si=HrmZ0HLwaLBPDurq', NULL),
(2, 'Christ est la lumière', 'https://youtu.be/dEgy7JS7uCM?si=OuxQfsQatm70KLPl', NULL),
(2, 'Hosanna, ouvrons les portes', 'https://youtu.be/Lrx1UVtftYc?si=mObtzmi2x_35Z--R', NULL),
(2, 'Yahwe (l''éternel est mon berger)', 'https://youtu.be/GWhy_fEg4mU?si=rxv6cet_pHLD-cTs', NULL),
(2, 'Louez Adonaï', 'https://youtu.be/3s3lh6rWpYg?si=6-yZdxNfbzgbW4cr', NULL),
(2, 'Tu es bon', 'https://youtu.be/WS12Smxk9o8?si=4507KQgJJFIvJID7', NULL),

-- CHANT DE SORTIE (type_id = 19)
(19, 'Par toute la terre', 'https://youtu.be/CpWILwqjICs?si=uEbiKEHvSIEbVbqM', NULL),
(19, 'Il est temps de quitter vos tombeaux', 'https://youtu.be/FBlBWdWYgdk?si=T6IiU1FptDTG916g', NULL),
(19, 'Comment ne pas te louer', 'https://youtu.be/RfcNHdVosus?si=A5KpNTuyY0DtG6HB', NULL),
(19, 'Vivre comme le Christ', 'https://youtu.be/ISpcSDiqCsY?si=bM5xM1GEoiq_LpOu', NULL),

-- ENTRÉE DES MARIÉS (type_id = 1)
(1, 'Aimer c''est tout donner', 'https://youtu.be/v7x9jqsmwf8?si=_Xk3worNOh_2OOFd', NULL),
(1, 'L''amour jamais ne passera', 'https://youtu.be/JiH5IXCO7VU?si=p9zLHaE21xaQSXNr', NULL),
(1, 'Je te donne tout', 'https://youtu.be/NHbBDCGa4sU?si=DvU13tn0og_y8Jgb', NULL),
(1, 'Cantique des cantiques', 'https://youtu.be/mzvJVoHEDH4?si=35jj686vxkIx3XYH', NULL),
(1, 'Comme l''argile', 'https://youtu.be/SxxXK05hjXA?si=EJSakUUDkiJ28Rtu', NULL),
(1, 'Ce nom est si merveilleux', 'https://youtu.be/l7P0Q6qI7cw?si=I7KxYEoM7JSJfwh6', NULL),
(1, 'Eveille toi mon âme', 'https://youtu.be/SaDXENya6OA?si=P-G_2VhrX3GGoxOZ', NULL),
(1, 'Hallelujah', 'https://youtu.be/y8AWFf7EAc4?si=n4aX4Y9PVGtK_Dxb', NULL),
(1, 'Amazing grace', 'https://youtu.be/HsCp5LG_zNE?si=HXnRTnS52lsAnTZl', NULL);

-- continuer de la même manière pour tous les autres chants : Offertoire, Gloria, Sanctus, Anamnèse, Agnus, Communion, Prière Universelle, etc.